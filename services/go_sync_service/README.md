# Go Sync Service (High-Performance Ingestion)

This microservice is responsible for high-speed synchronization between external marketplace APIs (WB, Ozon, etc.) and our Laravel application.

## 🚀 Key Features

*   **Concurrency:** Uses Go's goroutines to perform multiple API requests in parallel.
*   **Rate Limiting:** Implements strict token-bucket rate limiting to avoid getting banned by marketplace APIs.
*   **Asynchronous:** Communicates with Laravel via RabbitMQ queues.
*   **Reliability:** Implements retries with exponential backoff and graceful shutdown.

## 🛠 Tech Stack

*   **Language:** Go 1.23
*   **Message Broker:** RabbitMQ (AMQP 0.9.1)
*   **Libraries:**
    *   `github.com/rabbitmq/amqp091-go`: Official RabbitMQ client.
    *   `golang.org/x/time/rate`: Token bucket rate limiter.

## 📦 Service Architecture

1.  **Consumer:** Listens to the `sync.tasks` queue for incoming jobs from Laravel.
2.  **Dispatcher:** Distributes tasks to dedicated marketplace clients (WB, Ozon, etc.).
3.  **Client:** Handles HTTP requests, retries, and rate limiting.
4.  **Publisher:** Sends the processing results back to the `sync.results` queue for Laravel to ingest.

## 📬 Queue Message Formats

### Sync Task (Input - `sync.tasks`)
```json
{
  "organization_id": 1,
  "marketplace": "wb",
  "operation": "orders",
  "payload": { ... }
}
```

### Sync Result (Output - `sync.results`)
```json
{
  "organization_id": 1,
  "marketplace": "wb",
  "operation": "orders",
  "status": "success",
  "data": { ... },
  "error_message": "",
  "processed_at": "2024-05-20T12:34:56Z"
}
```

## 🛠 Local Development (Docker)

The service is part of the main `docker-compose.yml`.

```bash
# Build and run
docker compose up go_sync_service --build
```

## 📌 Environment Variables

*   `RABBITMQ_URL`: AMQP connection string (e.g., `amqp://guest:guest@rabbitmq:5672/`).
*   `APP_URL`: URL of the main Laravel application (used for internal callbacks if needed).
