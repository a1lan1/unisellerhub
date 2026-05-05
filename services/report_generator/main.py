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
TASK_QUEUE = "report.tasks"
RESULT_QUEUE = "sync.results"

def generate_excel(data, filename):
    """
    Generates an Excel file from a list of dictionaries.
    """
    df = pd.DataFrame(data)
    filepath = f"/tmp/{filename}"

    # Use XlsxWriter as the engine
    writer = pd.ExcelWriter(filepath, engine='xlsxwriter')
    df.to_excel(writer, sheet_name='Report', index=False)

    # Simple formatting
    workbook = writer.book
    worksheet = writer.sheets['Report']
    header_format = workbook.add_format({'bold': True, 'bg_color': '#D7E4BC', 'border': 1})

    for col_num, value in enumerate(df.columns.values):
        worksheet.write(0, col_num, value, header_format)

    writer.close()
    return filepath

def process_message(ch, method, properties, body):
    try:
        payload = json.loads(body)
        org_id = payload.get("organization_id")
        report_type = payload.get("report_type", "general")
        items = payload.get("data", [])

        logger.info("Generating report", extra={"organization_id": org_id, "type": report_type})

        start_time = time.time()
        filename = f"report_{org_id}_{int(time.time())}.xlsx"
        filepath = generate_excel(items, filename)
        duration = time.time() - start_time

        # In a real app, we would upload this to S3 and return the URL
        # For this prototype, we'll return the filename and simulated URL
        result = {
            "organization_id": org_id,
            "marketplace": "internal",
            "operation": "report_generation",
            "status": "success",
            "duration": duration,
            "data": {
                "report_type": report_type,
                "filename": filename,
                "download_url": f"/storage/reports/{filename}"
            },
            "processed_at": time.strftime('%Y-%m-%dT%H:%M:%SZ', time.gmtime())
        }

        ch.basic_publish(
            exchange='',
            routing_key=RESULT_QUEUE,
            body=json.dumps(result),
            properties=pika.BasicProperties(delivery_mode=2)
        )

        ch.basic_ack(delivery_tag=method.delivery_tag)
        logger.info("Report generated successfully", extra={"duration": duration})

    except Exception as e:
        logger.error("Error generating report", extra={"error": str(e)})
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

            logger.info("Report Generator Consumer started")
            channel.start_consuming()
        except Exception as e:
            logger.error("RabbitMQ connection lost", extra={"error": str(e)})
            time.sleep(5)

if __name__ == "__main__":
    start_consumer()
