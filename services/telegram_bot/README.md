# Telegram Bot Service (Go)

This service is responsible for sending asynchronous notifications to Telegram.

## 🚀 Key Features

*   **Asynchronous:** Listens to RabbitMQ `notifications.telegram` queue.
*   **HTML Support:** Supports HTML formatting for messages.
*   **Admin Alerts:** Specifically used for notifying admins about sync failures.

## 🛠 Tech Stack

*   **Language:** Go 1.23
*   **Library:** `github.com/go-telegram-bot-api/telegram-bot-api/v5`

## 📬 Queue Interface

### Input Task (`notifications.telegram`)
```json
{
  "chat_id": 12345678,
  "text": "⚠️ <b>Sync Failed</b>\nError: API Timeout",
  "parse_mode": "HTML"
}
```

## 🛠 Local Development

```bash
docker compose up telegram_bot --build
```

## 📌 Environment Variables

*   `TELEGRAM_BOT_TOKEN`: Your Telegram Bot API token.
*   `RABBITMQ_URL`: AMQP connection string.
