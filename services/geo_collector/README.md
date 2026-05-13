# Geo Collector Service

This service is responsible for collecting reviews from various geo-services (like Google, Yelp, etc.), analyzing their sentiment, and publishing them to a RabbitMQ queue for further processing by the main application.

## Purpose

- **Collect Reviews**: In a real-world scenario, this service would connect to external APIs (e.g., Google Places API) to fetch new reviews for registered business locations.
- **Analyze Sentiment**: Performs basic sentiment analysis on the review text to classify it as positive, neutral, or negative.
- **Publish to RabbitMQ**: Pushes the collected and analyzed review data into the `geo_reviews` RabbitMQ queue.

## How it works

The `geo_collector` service acts as an entry point for external review data and integrates with the main Laravel application via RabbitMQ:

1.  **Receive Reviews**: The service exposes a `POST /collect_reviews` endpoint to receive review data, typically from external webhooks or scheduled jobs.
2.  **Sentiment Analysis**: Upon receiving reviews, it performs sentiment analysis on the review text using `TextBlob` to determine if the review is "positive", "negative", or "neutral".
3.  **Publish to RabbitMQ**: The original review data, augmented with the sentiment analysis result, is then published as a JSON message to the `geo_reviews` queue in RabbitMQ.
4.  **Laravel Consumption**: The main Laravel application has a dedicated Artisan command (`php artisan rabbitmq:consume-geo-reviews`) running as a consumer. This command listens to the `geo_reviews` queue.
5.  **Process Review**: When the Laravel consumer receives a message, it deserializes the JSON into a `ReviewData` DTO and dispatches a `ProcessGeoReview` job.
6.  **Store Data**: The `ProcessGeoReview` job, in turn, uses the `StoreReviewAction` to persist the review data (including sentiment) into the application's database.

## API

### `POST /collect_reviews`

This endpoint allows for receiving review data. It's primarily used for testing and can be used for integrations that push data instead of being pulled.

**Request Body:**

```json
[
  {
    "location_id": 1,
    "source": "google",
    "external_id": "unique-review-id-123",
    "author_name": "John Doe",
    "text": "This place is amazing!",
    "rating": 5,
    "published_at": "2023-10-27T10:00:00Z"
  }
]
```

**Response:**

```json
{
  "status": "success",
  "processed": 1
}
```

### `GET /health`

A simple health check endpoint. Returns `{"status": "ok"}` if the service is running.
