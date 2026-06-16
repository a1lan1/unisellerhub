### 4. Geo Collector Service (Python/FastAPI)

- **Purpose**: A service designed to collect reviews from various geo-services (like Google, Yelp, etc.).
- **Technologies**: Python, FastAPI, `confluent-kafka-python`.
- **How it works**:
    - **API Endpoint**: Exposes a `POST /collect_reviews` endpoint that accepts review data. In a real-world scenario, this service would contain schedulers to periodically pull data from external APIs.
    - **Sentiment Analysis**: Performs basic sentiment analysis on the review text.
    - **Publishing**: Publishes the enriched review data to the `geo_reviews` topic.
- **Usage**:
    - **Laravel Consumer**: A dedicated Laravel consumer (`geo_reviews_consumer`) listens to the `geo_reviews` topic and dispatches a `ProcessGeoReview` job to store the data in the database.
