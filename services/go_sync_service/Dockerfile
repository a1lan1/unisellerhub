# Build stage
FROM golang:1.23-alpine AS builder

WORKDIR /app

# Install git for potential dependencies
RUN apk add --no-cache git

COPY go.mod ./
COPY . .

# Run tidy AFTER copying source code to ensure go.sum is accurate
RUN go mod tidy && go mod download

# Build the application
RUN CGO_ENABLED=0 GOOS=linux go build -o go_sync_service ./cmd/syncer/main.go

# Run stage
FROM alpine:latest

WORKDIR /app

# Install certificates for HTTPS requests to external APIs
RUN apk --no-cache add ca-certificates tzdata

COPY --from=builder /app/go_sync_service .

CMD ["./go_sync_service"]
