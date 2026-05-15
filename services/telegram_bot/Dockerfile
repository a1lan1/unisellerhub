# Build stage
FROM golang:1.23-alpine AS builder

WORKDIR /app

RUN apk add --no-cache git

COPY go.mod ./
COPY . .

# Run tidy AFTER copying source code to ensure go.sum is accurate
RUN go mod tidy && go mod download

RUN CGO_ENABLED=0 GOOS=linux go build -o telegram-bot ./main.go

# Run stage
FROM alpine:latest

WORKDIR /app

RUN apk --no-cache add ca-certificates tzdata

COPY --from=builder /app/telegram-bot .

CMD ["./telegram-bot"]
