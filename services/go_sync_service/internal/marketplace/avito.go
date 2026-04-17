package marketplace

import (
	"context"
	"encoding/json"
	"fmt"
	"net/http"
)

type AvitoClient struct {
	client *Client
}

func NewAvitoClient(baseURL, apiKey string) *AvitoClient {
	return &AvitoClient{
		client: NewClient(baseURL, apiKey, 5),
	}
}

type AvitoStock struct {
	ItemID   int    `json:"item_id"`
	Quantity int    `json:"quantity"`
	WhID     string `json:"warehouse_id"`
}

type AvitoProduct struct {
	ID    int    `json:"id"`
	Title string `json:"title"`
	Price string `json:"price"` // returns string price "199.90"
}

type AvitoOrder struct {
	ID         string  `json:"id"`
	Status     string  `json:"status"`
	CreatedAt  string  `json:"createdAt"`
	TotalPrice string  `json:"totalPrice"`
	Items      []struct {
		ID    string  `json:"id"`
		Title string  `json:"title"`
		Price string  `json:"price"`
		Count int     `json:"count"`
	} `json:"items"`
}

func (a *AvitoClient) GetStocks(ctx context.Context, token string) ([]AvitoStock, error) {
	headers := map[string]string{"Authorization": "Bearer " + token}
	data, err := a.client.DoRequest(ctx, http.MethodGet, "/items/v1/stocks", nil, headers)
	if err != nil {
		return nil, fmt.Errorf("Avito stocks error: %w", err)
	}

	var resp struct {
		Stocks []AvitoStock `json:"stocks"`
	}
	if err := json.Unmarshal(data, &resp); err != nil {
		return nil, err
	}

	return resp.Stocks, nil
}

func (a *AvitoClient) GetProducts(ctx context.Context, token string) ([]AvitoProduct, error) {
	headers := map[string]string{"Authorization": "Bearer " + token}
	data, err := a.client.DoRequest(ctx, http.MethodGet, "/items/v2/list", nil, headers)
	if err != nil {
		return nil, fmt.Errorf("Avito products error: %w", err)
	}

	var resp struct {
		Resources []AvitoProduct `json:"resources"`
	}
	if err := json.Unmarshal(data, &resp); err != nil {
		return nil, err
	}

	return resp.Resources, nil
}

func (a *AvitoClient) GetOrders(ctx context.Context, token string) ([]AvitoOrder, error) {
	headers := map[string]string{"Authorization": "Bearer " + token}
	data, err := a.client.DoRequest(ctx, http.MethodGet, "/order/v1/list", nil, headers)
	if err != nil {
		return nil, fmt.Errorf("Avito orders error: %w", err)
	}

	var resp struct {
		Orders []AvitoOrder `json:"orders"`
	}
	if err := json.Unmarshal(data, &resp); err != nil {
		return nil, err
	}

	return resp.Orders, nil
}
