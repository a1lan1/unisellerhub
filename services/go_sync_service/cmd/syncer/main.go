package main

import (
	"context"
	"encoding/json"
	"fmt"
	"log/slog"
	"os"
	"os/signal"
	"strconv"
	"syscall"
	"time"

	"go_sync_service/internal/marketplace"

	amqp "github.com/rabbitmq/amqp091-go"
)

type SyncTask struct {
	OrganizationID int            `json:"organization_id"`
	Marketplace    string         `json:"marketplace"`
	Operation      string         `json:"operation"` // orders, products, inventory
	Payload        map[string]any `json:"payload,omitempty"`
}

type SyncResult struct {
	OrganizationID int     `json:"organization_id"`
	Marketplace    string  `json:"marketplace"`
	Operation      string  `json:"operation"`
	Status         string  `json:"status"` // success, error
	Data           any     `json:"data,omitempty"`
	Duration       float64 `json:"duration"` // In seconds
	ErrorMessage   string  `json:"error_message,omitempty"`
	ProcessedAt    string  `json:"processed_at"`
}

func main() {
	// Initialize JSON logging
	logger := slog.New(slog.NewJSONHandler(os.Stderr, nil))
	slog.SetDefault(logger)

	rabbitURL := os.Getenv("RABBITMQ_URL")
	if rabbitURL == "" {
		rabbitURL = "amqp://guest:guest@localhost:5672/"
	}

	// Fetch base URLs from environment
	wbURL := os.Getenv("WB_BASE_URL")
	ozonURL := os.Getenv("OZON_BASE_URL")
	ymURL := os.Getenv("YANDEX_BASE_URL")
	avitoURL := os.Getenv("AVITO_BASE_URL")
	msURL := os.Getenv("MOYSKLAD_BASE_URL")

	// 1. Initialize Clients
	clients := map[string]interface{}{
		"wb":     marketplace.NewWildberriesClient(wbURL, ""),
		"ozon":   marketplace.NewOzonClient(ozonURL, ""),
		"yandex": marketplace.NewYandexClient(ymURL, ""),
		"avito":  marketplace.NewAvitoClient(avitoURL, ""),
		"ms":     marketplace.NewMoySkladClient(msURL, ""),
	}

	// 2. Connect to RabbitMQ
	var conn *amqp.Connection
	var err error
	for i := 0; i < 10; i++ {
		conn, err = amqp.Dial(rabbitURL)
		if err == nil {
			break
		}
		slog.Warn("Failed to connect to RabbitMQ, retrying...", "attempt", i+1, "error", err)
		time.Sleep(5 * time.Second)
	}
	if err != nil {
		slog.Error("Could not connect to RabbitMQ", "error", err)
		os.Exit(1)
	}
	defer conn.Close()

	ch, err := conn.Channel()
	if err != nil {
		slog.Error("Failed to open a channel", "error", err)
		os.Exit(1)
	}
	defer ch.Close()

	// 3. Declare Queues
	tasksQueue, _ := ch.QueueDeclare("sync.tasks", true, false, false, false, nil)
	resultsQueue, _ := ch.QueueDeclare("sync.results", true, false, false, false, nil)

	// 4. Setup Consumer
	msgs, err := ch.Consume(tasksQueue.Name, "go-syncer", false, false, false, false, nil)
	if err != nil {
		slog.Error("Failed to register a consumer", "error", err)
		os.Exit(1)
	}

	// 5. Handle Shutdown
	ctx, cancel := context.WithCancel(context.Background())
	defer cancel()

	sigChan := make(chan os.Signal, 1)
	signal.Notify(sigChan, syscall.SIGINT, syscall.SIGTERM)

	go func() {
		for msg := range msgs {
			var task SyncTask
			if err := json.Unmarshal(msg.Body, &task); err != nil {
				slog.Error("Error unmarshaling task", "error", err)
				msg.Reject(false)
				continue
			}

			slog.Info("Processing task",
				"operation", task.Operation,
				"organization_id", task.OrganizationID,
				"marketplace", task.Marketplace,
			)

			startTime := time.Now()
			var resultData any
			var syncErr error

			// Helper to get credentials from payload
			getCred := func(key string) string {
				if task.Payload == nil {
					return ""
				}
				if val, ok := task.Payload[key].(string); ok {
					return val
				}
				return ""
			}

			// 6. Marketplace Dispatcher
			switch task.Marketplace {
			case "wb":
				if client, ok := clients["wb"].(*marketplace.WildberriesClient); ok {
					client.SetToken(getCred("token"))
					switch task.Operation {
					case "inventory":
						resultData, syncErr = client.GetStocks(ctx)
					case "products":
						resultData, syncErr = client.GetProducts(ctx)
					case "orders":
						resultData, syncErr = client.GetOrders(ctx)
					}
				}
			case "ozon":
				if client, ok := clients["ozon"].(*marketplace.OzonClient); ok {
					clientId := getCred("client_id")
					apiKey := getCred("api_key")
					switch task.Operation {
					case "inventory":
						resultData, syncErr = client.GetStocks(ctx, clientId, apiKey)
					case "products":
						resultData, syncErr = client.GetProducts(ctx, clientId, apiKey)
					case "orders":
						resultData, syncErr = client.GetOrders(ctx, clientId, apiKey)
					}
				}
			case "yandex":
				if client, ok := clients["yandex"].(*marketplace.YandexClient); ok {
					apiKey := getCred("api_key")
					businessId := 0
					if val, ok := task.Payload["business_id"].(float64); ok {
						businessId = int(val)
					} else if val, ok := task.Payload["business_id"].(string); ok {
						businessId, _ = strconv.Atoi(val)
					}

					campaignId := 0
					if val, ok := task.Payload["campaign_id"].(float64); ok {
						campaignId = int(val)
					} else if val, ok := task.Payload["campaign_id"].(string); ok {
						campaignId, _ = strconv.Atoi(val)
					}

					switch task.Operation {
					case "inventory":
						resultData, syncErr = client.GetStocks(ctx, businessId, apiKey)
					case "products":
						resultData, syncErr = client.GetProducts(ctx, businessId, apiKey)
					case "orders":
						resultData, syncErr = client.GetOrders(ctx, campaignId, apiKey)
					}
				}
			case "avito":
				if client, ok := clients["avito"].(*marketplace.AvitoClient); ok {
					token := getCred("client_id")
					switch task.Operation {
					case "inventory":
						resultData, syncErr = client.GetStocks(ctx, token)
					case "products":
						resultData, syncErr = client.GetProducts(ctx, token)
					case "orders":
						resultData, syncErr = client.GetOrders(ctx, token)
					}
				}
			case "ms":
				if client, ok := clients["ms"].(*marketplace.MoySkladClient); ok {
					token := getCred("ms_token")
					switch task.Operation {
					case "inventory":
						resultData, syncErr = client.GetStocks(ctx, token)
					case "products":
						resultData, syncErr = client.GetProducts(ctx, token)
					case "orders":
						resultData, syncErr = client.GetOrders(ctx, token)
					}
				}
			default:
				syncErr = fmt.Errorf("unsupported marketplace: %s", task.Marketplace)
			}

			duration := time.Since(startTime).Seconds()

			// 7. Publish Result
			status := "success"
			errMsg := ""
			if syncErr != nil {
				status = "error"
				errMsg = syncErr.Error()
				slog.Error("Sync operation failed",
					"marketplace", task.Marketplace,
					"operation", task.Operation,
					"error", syncErr,
				)
			} else {
				// DEBUG: Log sample of data being sent back
				slog.Info("Task processed successfully",
					"marketplace", task.Marketplace,
					"operation", task.Operation,
					"duration_sec", duration,
					"data_sample", resultData,
				)
			}

			result := SyncResult{
				OrganizationID: task.OrganizationID,
				Marketplace:    task.Marketplace,
				Operation:      task.Operation,
				Status:         status,
				Data:           resultData,
				Duration:       duration,
				ErrorMessage:   errMsg,
				ProcessedAt:    time.Now().Format(time.RFC3339),
			}

			resultJSON, _ := json.Marshal(result)
			_ = ch.PublishWithContext(ctx, "", resultsQueue.Name, false, false, amqp.Publishing{
				ContentType: "application/json",
				Body:        resultJSON,
			})

			msg.Ack(false)
		}
	}()

	slog.Info("Go Sync Service is running with JSON logging enabled")
	<-sigChan
	slog.Info("Shutting down Go Sync Service...")
}
