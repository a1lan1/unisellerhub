package limiter

import (
	"context"
	"golang.org/x/time/rate"
	"time"
)

// Limiter wraps the golang.org/x/time/rate limiter
type Limiter struct {
	rateLimiter *rate.Limiter
}

// NewLimiter creates a new rate limiter with requests per second and burst capacity
func NewLimiter(rps int, burst int) *Limiter {
	return &Limiter{
		rateLimiter: rate.NewLimiter(rate.Limit(rps), burst),
	}
}

// Wait blocks until the limiter allows a request or the context is cancelled
func (l *Limiter) Wait(ctx context.Context) error {
	return l.rateLimiter.Wait(ctx)
}

// NewMinuteLimiter creates a limiter based on requests per minute
func NewMinuteLimiter(rpm int, burst int) *Limiter {
	limit := rate.Every(time.Minute / time.Duration(rpm))
	return &Limiter{
		rateLimiter: rate.NewLimiter(limit, burst),
	}
}
