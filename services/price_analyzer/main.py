import os
import json
import logging
import pika
import pandas as pd
import time
import threading
from pythonjsonlogger import jsonlogger

# Logging setup
logger = logging.getLogger()
logHandler = logging.StreamHandler()
formatter = jsonlogger.JsonFormatter('%(timestamp)s %(level)s %(name)s %(message)s')
logHandler.setFormatter(formatter)
logger.addHandler(logHandler)
logger.setLevel(logging.INFO)

# RabbitMQ configuration
RABBITMQ_HOST = os.getenv("RABBITMQ_HOST", "rabbitmq")
RABBITMQ_USER = os.getenv("RABBITMQ_USER", "guest")
RABBITMQ_PASS = os.getenv("RABBITMQ_PASS", "guest")
TASK_QUEUE = "price.tasks"
RESULT_QUEUE = "report.results"

def calculate_velocity(sales_history, current_stock):
    if not sales_history:
        return {"avg_daily_sales": 0.0, "days_left": 999, "trend": "flat"}

    df = pd.DataFrame(sales_history)
    # Ensure 'date' column is datetime and set as index for resampling
    df['date'] = pd.to_datetime(df['date'])
    df = df.set_index('date')

    # Resample to daily sales and fill missing days with 0
    daily_sales = df['quantity'].resample('D').sum().fillna(0)

    avg_daily_sales = daily_sales.mean() if not daily_sales.empty else 0.0
    days_left = int(current_stock / avg_daily_sales) if avg_daily_sales > 0 else 999

    trend = "stable"
    if len(daily_sales) >= 5: # Need at least 5 days to compare head and tail
        head_mean = daily_sales.head(3).mean()
        tail_mean = daily_sales.tail(3).mean()
        if tail_mean > head_mean:
            trend = "increasing"
        elif tail_mean < head_mean:
            trend = "decreasing"


    return {
        "avg_daily_sales": round(float(avg_daily_sales), 2),
        "days_left": days_left,
        "trend": trend,
        "current_stock": current_stock
    }

def process_message(ch, method, properties, body):
    try:
        taskData = json.loads(body)
        payload = taskData.get("payload")

        # Expect payload to be a list of product data
        if not isinstance(payload, list):
            logger.error("Invalid payload format: Expected a list of products", extra={"payload": payload})
            ch.basic_nack(delivery_tag=method.delivery_tag, requeue=False)
            return

        batch_id = None
        organization_id = None
        results = []

        for data in payload:
            # Extract batch_id and organization_id from the first item, assuming they are consistent across the batch
            if batch_id is None:
                batch_id = data.get("batch_id")
            if organization_id is None:
                organization_id = data.get("organization_id")

            sku = data.get("sku")
            product_id = data.get("product_id")
            marketplace = data.get("marketplace")

            logger.info("Analyzing prices for batch item", extra={
                "organization_id": organization_id,
                "sku": sku,
                "batch_id": batch_id
            })

            start_time = time.time()
            stats = calculate_velocity(
                data.get('sales_history', []),
                data.get('current_stock', 0)
            )
            duration = time.time() - start_time

            results.append({
                "organization_id": organization_id,
                "marketplace": marketplace,
                "operation": "price_analysis_item", # Indicate this is an item result within a batch
                "status": "success",
                "duration": duration,
                "data": {
                    "sku": sku,
                    "product_id": product_id,
                    "stats": stats
                },
                "processed_at": time.strftime('%Y-%m-%dT%H:%M:%SZ', time.gmtime()),
                "batch_id": batch_id,
            })

        # Send a single aggregated result for the entire batch
        final_batch_result = {
            "organization_id": organization_id,
            "operation": "price_analysis_batch", # New operation type for batch results
            "status": "success",
            "batch_id": batch_id,
            "data": results, # Array of individual product results
            "processed_at": time.strftime('%Y-%m-%dT%H:%M:%SZ', time.gmtime())
        }

        ch.basic_publish(
            exchange='',
            routing_key=RESULT_QUEUE,
            body=json.dumps(final_batch_result),
            properties=pika.BasicProperties(delivery_mode=2)
        )

        ch.basic_ack(delivery_tag=method.delivery_tag)
        logger.info("Price analysis batch completed", extra={"batch_id": batch_id, "items_count": len(results)})

    except Exception as e:
        logger.error("Error analyzing price batch", extra={"error": str(e), "payload": body.decode()})
        ch.basic_nack(delivery_tag=method.delivery_tag, requeue=False)

def start_consumer():
    while True:
        try:
            credentials = pika.PlainCredentials(RABBITMQ_USER, RABBITMQ_PASS)
            connection = pika.BlockingConnection(pika.ConnectionParameters(host=RABBITMQ_HOST, credentials=credentials))
            channel = connection.channel()

            channel.queue_declare(queue=TASK_QUEUE, durable=True)
            channel.queue_declare(queue=RESULT_QUEUE, durable=True)

            channel.basic_qos(prefetch_count=1)
            channel.basic_consume(queue=TASK_QUEUE, on_message_callback=process_message)

            logger.info("Price Analyzer Consumer started")
            channel.start_consuming()
        except Exception as e:
            logger.error("RabbitMQ connection lost", extra={"error": str(e)})
            time.sleep(5)

if __name__ == "__main__":
    start_consumer()
