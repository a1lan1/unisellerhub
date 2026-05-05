FROM python:3.12-slim

WORKDIR /app

# 1. Install and update CA certificates first
RUN apt-get update && apt-get install -y --no-install-recommends \
    ca-certificates \
    build-essential \
    && update-ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# 2. Set PIP environment variables
ENV PIP_DEFAULT_TIMEOUT=100 \
    PIP_RETRIES=20 \
    PIP_DISABLE_PIP_VERSION_CHECK=1

COPY requirements.txt .

# 3. Install requirements
RUN pip install --no-cache-dir -r requirements.txt

COPY . .

# Create tmp directory for reports
RUN mkdir -p /tmp/reports && chmod 777 /tmp/reports

CMD ["python", "main.py"]
