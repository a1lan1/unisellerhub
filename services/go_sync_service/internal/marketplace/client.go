package marketplace

import (
	"context"
	"fmt"
	"io"
	"math"
	"net/http"
	"time"

	"go_sync_service/internal/limiter"
)

type Client struct {
	httpClient  *http.Client
	limiter     *limiter.Limiter
	baseURL     string
	apiKey      string
	maxRetries  int
	backoffBase time.Duration
}

func NewClient(baseURL string, apiKey string, rps int) *Client {
	return &Client{
		httpClient:  &http.Client{Timeout: 30 * time.Second},
		limiter:     limiter.NewLimiter(rps, rps*2),
		baseURL:     baseURL,
		apiKey:      apiKey,
		maxRetries:  3,
		backoffBase: 1 * time.Second,
	}
}

// SetAPIKey allows updating the API key dynamically per request context if needed,
// but for our current architecture, we'll pass it to DoRequest or create new clients.
func (c *Client) SetAPIKey(key string) {
	c.apiKey = key
}

func (c *Client) DoRequest(ctx context.Context, method, path string, body io.Reader, headers map[string]string) ([]byte, error) {
	var lastErr error

	for i := 0; i <= c.maxRetries; i++ {
		// 1. Wait for rate limiter
		if err := c.limiter.Wait(ctx); err != nil {
			return nil, fmt.Errorf("rate limit error: %w", err)
		}

		// 2. Prepare request
		url := fmt.Sprintf("%s%s", c.baseURL, path)
		req, err := http.NewRequestWithContext(ctx, method, url, body)
		if err != nil {
			return nil, err
		}

		// Set default headers
		req.Header.Set("Content-Type", "application/json")
		if c.apiKey != "" {
			req.Header.Set("Authorization", "Bearer "+c.apiKey)
		}

		// Override/Add custom headers (e.g., Ozon Client-Id)
		for k, v := range headers {
			req.Header.Set(k, v)
		}

		// 3. Execute request
		resp, err := c.httpClient.Do(req)
		if err != nil {
			lastErr = err
			c.waitBeforeRetry(i)
			continue
		}

		// 4. Handle response
		defer resp.Body.Close()

		if resp.StatusCode == http.StatusOK {
			return io.ReadAll(resp.Body)
		}

		// Retry only on 429 (Rate Limit) or 5xx (Server Error)
		if resp.StatusCode == http.StatusTooManyRequests || resp.StatusCode >= 500 {
			lastErr = fmt.Errorf("request failed with status: %d", resp.StatusCode)
			c.waitBeforeRetry(i)
			continue
		}

		// For 4xx errors (except 429), don't retry
		return nil, fmt.Errorf("request failed with status: %d", resp.StatusCode)
	}

	return nil, fmt.Errorf("max retries exceeded. last error: %v", lastErr)
}

func (c *Client) waitBeforeRetry(attempt int) {
	if attempt >= c.maxRetries {
		return
	}
	// Exponential backoff: 1s, 2s, 4s...
	backoff := time.Duration(math.Pow(2, float64(attempt))) * c.backoffBase
	time.Sleep(backoff)
}
