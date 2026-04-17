package marketplace

import (
	"context"
	"encoding/json"
	"fmt"
	"net/http"
)

type WildberriesClient struct {
	client *Client
}

func NewWildberriesClient(baseURL, apiKey string) *WildberriesClient {
	return &WildberriesClient{
		client: NewClient(baseURL, apiKey, 10),
	}
}

func (w *WildberriesClient) SetToken(token string) {
	w.client.SetAPIKey(token)
}

type WBStock struct {
	NmID   interface{} `json:"nmId"`
	Sku    string      `json:"sku"`
	Amount int         `json:"amount"`
	WhName string      `json:"warehouseName"`
}

type WBProduct struct {
	NmID        interface{} `json:"nmId"`
	VendorCode  string      `json:"vendorCode"`
	Title       string      `json:"title"`
	Description string      `json:"description"`
	Price       int64       `json:"price"`
}

type WBOrder struct {
	ID        interface{} `json:"id"`
	Status    string      `json:"status"`
	CreatedAt string      `json:"createdAt"`
	Price     int64       `json:"price"`
	Items     []struct {
		NmID     interface{} `json:"nmId"`
		Sku      string      `json:"sku"`
		Quantity int         `json:"quantity"`
		Price    int64       `json:"price"`
	} `json:"items"`
}

func (w *WildberriesClient) GetStocks(ctx context.Context) ([]WBStock, error) {
	data, err := w.client.DoRequest(ctx, http.MethodGet, "/api/v3/stocks", nil, nil)
	if err != nil {
		return nil, fmt.Errorf("WB stocks error: %w", err)
	}
	var resp struct {
		Stocks []WBStock `json:"stocks"`
	}
	json.Unmarshal(data, &resp)
	return resp.Stocks, nil
}

func (w *WildberriesClient) GetProducts(ctx context.Context) ([]WBProduct, error) {
	data, err := w.client.DoRequest(ctx, http.MethodPost, "/content/v2/get/cards/list", nil, nil)
	if err != nil {
		return nil, fmt.Errorf("WB products error: %w", err)
	}
	var resp struct {
		Cards []WBProduct `json:"cards"`
	}
	json.Unmarshal(data, &resp)
	return resp.Cards, nil
}

func (w *WildberriesClient) GetOrders(ctx context.Context) ([]WBOrder, error) {
	data, err := w.client.DoRequest(ctx, http.MethodGet, "/api/v3/orders", nil, nil)
	if err != nil {
		return nil, fmt.Errorf("WB orders error: %w", err)
	}
	var resp struct {
		Orders []WBOrder `json:"orders"`
	}
	json.Unmarshal(data, &resp)
	return resp.Orders, nil
}
