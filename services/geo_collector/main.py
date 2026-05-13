import os
import json
import logging
import asyncio
from fastapi import FastAPI
from pydantic import BaseModel
from typing import List, Optional, Union
from textblob import TextBlob
import aio_pika
from pythonjsonlogger import jsonlogger

# Logging setup
logger = logging.getLogger()
logHandler = logging.StreamHandler()
formatter = jsonlogger.JsonFormatter('%(timestamp)s %(level)s %(name)s %(message)s')
logHandler.setFormatter(formatter)
logger.addHandler(logHandler)
logger.setLevel(logging.INFO)

app = FastAPI()

# RabbitMQ configuration
RABBITMQ_URL = os.getenv("RABBITMQ_URL", "amqp://guest:guest@rabbitmq:5672/")
RABBITMQ_QUEUE = os.getenv("RABBITMQ_QUEUE", "geo_reviews")

rabbitmq_connection: Optional[aio_pika.Connection] = None
rabbitmq_channel: Optional[aio_pika.Channel] = None

class Review(BaseModel):
    location_id: Union[int, None] = None
    source: str
    external_id: str
    author_name: str
    text: Optional[str]
    rating: int
    published_at: str

def analyze_sentiment(text: str) -> str:
    if not text:
        return "neutral"
    analysis = TextBlob(text)
    if analysis.sentiment.polarity > 0.1:
        return "positive"
    elif analysis.sentiment.polarity < -0.1:
        return "negative"
    else:
        return "neutral"

@app.on_event("startup")
async def startup_event():
    logger.info("Starting Geo Collector Service...")
    global rabbitmq_connection, rabbitmq_channel
    try:
        rabbitmq_connection = await aio_pika.connect_robust(RABBITMQ_URL)
        rabbitmq_channel = await rabbitmq_connection.channel()
        await rabbitmq_channel.declare_queue(RABBITMQ_QUEUE, durable=True)
        logger.info("Connected to RabbitMQ and declared queue.")
    except Exception as e:
        logger.error("Failed to connect to RabbitMQ", extra={"error": str(e)})
        raise

@app.on_event("shutdown")
async def shutdown_event():
    logger.info("Shutting down Geo Collector Service...")
    if rabbitmq_channel:
        await rabbitmq_channel.close()
    if rabbitmq_connection:
        await rabbitmq_connection.close()
    logger.info("Disconnected from RabbitMQ.")

@app.post("/collect_reviews")
async def collect_reviews(reviews: List[Review]):
    """
    Endpoint for receiving reviews from external sources
    """
    if not rabbitmq_channel:
        logger.error("RabbitMQ channel is not available.")
        return {"status": "error", "message": "RabbitMQ not connected"}, 500

    processed_count = 0
    for review in reviews:
        sentiment = analyze_sentiment(review.text)

        if hasattr(review, 'model_dump'):
            message = review.model_dump()
        else:
            message = review.dict()

        message['sentiment'] = sentiment

        try:
            await rabbitmq_channel.default_exchange.publish(
                aio_pika.Message(body=json.dumps(message).encode()),
                routing_key=RABBITMQ_QUEUE,
            )
            processed_count += 1
            logger.info("Review published to RabbitMQ", extra={"external_id": review.external_id, "source": review.source, "sentiment": sentiment})
        except Exception as e:
            logger.error("Failed to publish message to RabbitMQ", extra={"error": str(e), "review": message})

    return {"status": "success", "processed": processed_count}

@app.get("/health")
def health_check():
    return {"status": "ok"}

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8003)
