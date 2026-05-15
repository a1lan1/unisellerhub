package main

import (
	"encoding/json"
	"log"
	"os"
	"os/signal"
	"syscall"
	"time"

	tgbotapi "github.com/go-telegram-bot-api/telegram-bot-api/v5"
	amqp "github.com/rabbitmq/amqp091-go"
)

type TelegramMessage struct {
	ChatID  int64  `json:"chat_id"`
	Text    string `json:"text"`
	ParseMode string `json:"parse_mode,omitempty"`
}

func main() {
	botToken := os.Getenv("TELEGRAM_BOT_TOKEN")
	rabbitURL := os.Getenv("RABBITMQ_URL")
	if rabbitURL == "" {
		rabbitURL = "amqp://guest:guest@rabbitmq:5672/"
	}

	if botToken == "" {
		log.Println("TELEGRAM_BOT_TOKEN is not set. Service will run in log-only mode.")
	}

	// 1. Initialize Bot
	var bot *tgbotapi.BotAPI
	if botToken != "" {
		var err error
		bot, err = tgbotapi.NewBotAPI(botToken)
		if err != nil {
			log.Panic(err)
		}
		log.Printf("Authorized on account %s", bot.Self.UserName)
	}

	// 2. Connect to RabbitMQ
	var conn *amqp.Connection
	var err error
	for i := 0; i < 10; i++ {
		conn, err = amqp.Dial(rabbitURL)
		if err == nil {
			break
		}
		log.Printf("Failed to connect to RabbitMQ (attempt %d/10): %v", i+1, err)
		time.Sleep(5 * time.Second)
	}
	if err != nil {
		log.Fatal(err)
	}
	defer conn.Close()

	ch, err := conn.Channel()
	if err != nil {
		log.Fatal(err)
	}
	defer ch.Close()

	q, err := ch.QueueDeclare("notifications.telegram", true, false, false, false, nil)
	if err != nil {
		log.Fatal(err)
	}

	msgs, err := ch.Consume(q.Name, "telegram-bot", false, false, false, false, nil)
	if err != nil {
		log.Fatal(err)
	}

	// 3. Handle messages
	sigChan := make(chan os.Signal, 1)
	signal.Notify(sigChan, syscall.SIGINT, syscall.SIGTERM)

	go func() {
		for msg := range msgs {
			var tgMsg TelegramMessage
			if err := json.Unmarshal(msg.Body, &tgMsg); err != nil {
				log.Printf("Error decoding message: %v", err)
				msg.Ack(false)
				continue
			}

			if bot != nil {
				m := tgbotapi.NewMessage(tgMsg.ChatID, tgMsg.Text)
				if tgMsg.ParseMode != "" {
					m.ParseMode = tgMsg.ParseMode
				}
				_, err := bot.Send(m)
				if err != nil {
					log.Printf("Failed to send Telegram message: %v", err)
				} else {
					log.Printf("Message sent to ChatID: %d", tgMsg.ChatID)
				}
			} else {
				log.Printf("LOG-ONLY MODE: Message for ChatID %d: %s", tgMsg.ChatID, tgMsg.Text)
			}

			msg.Ack(false)
		}
	}()

	log.Println("Telegram Bot Service is running. Waiting for messages...")
	<-sigChan
}
