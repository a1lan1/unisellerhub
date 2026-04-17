package marketplace

import (
	"context"
	"encoding/json"
	"fmt"
	"net/http"
	"strings"
)

type YandexClient struct {
	client *Client
}

func NewYandexClient(baseURL, apiKey string) *YandexClient {
	return &YandexClient{
		client: NewClient(baseURL, apiKey, 10),
	}
}

type YandexStock struct {
	OfferID string `json:"offerId"`
	WarehouseStocks []struct {
		WarehouseID int `json:"warehouseId"`
		Count int `json:"count"`
	} `json:"warehouseStocks"`
}

type YandexProduct struct {
	Offer struct {
		OfferID string `json:"offerId"`
		Name    string `json:"name"`
		Price   int64  `json:"price"`
	} `json:"offer"`
	Mapping struct {
		MarketSku int `json:"marketSku"`
	} `json:"mapping"`
}

type YandexOrder struct {
	ID int `json:"id"`
	Status string `json:"status"`
	CreationDate string `json:"creationDate"`
	Items []struct {
		OfferID string `json:"offerId"`
		Sku string `json:"sku"`
		Count int `json:"count"`
		Price int64 `json:"price"`
	} `json:"items"`
	TotalPrice int64 `json:"totalPrice"`
}

func (y *YandexClient) GetStocks(ctx context.Context, businessId int, apiKey string) ([]YandexStock, error) {
	path := fmt.Sprintf("/v2/businesses/%d/offers/stocks", businessId)
	headers := map[string]string{"Api-Key": apiKey}
	data, err := y.client.DoRequest(ctx, http.MethodPost, path, strings.NewReader(`{}`), headers)
	if err != nil {
		return nil, err
	}
	var resp struct {
		Result struct {
			Offers []YandexStock `json:"offers"`
		} `json:"result"`
	}
	json.Unmarshal(data, &resp)
	return resp.Result.Offers, nil
}

func (y *YandexClient) GetProducts(ctx context.Context, businessId int, apiKey string) ([]YandexProduct, error) {
	path := fmt.Sprintf("/v2/businesses/%d/offer-mappings", businessId)
	headers := map[string]string{"Api-Key": apiKey}
	data, err := y.client.DoRequest(ctx, http.MethodPost, path, strings.NewReader(`{}`), headers)
	if err != nil {
		return nil, err
	}
	var resp struct {
		Result struct {
			Offers []YandexProduct `json:"offers"`
		} `json:"result"`
	}
	json.Unmarshal(data, &resp)
	return resp.Result.Offers, nil
}

func (y *YandexClient) GetOrders(ctx context.Context, campaignId int, apiKey string) ([]YandexOrder, error) {
	path := fmt.Sprintf("/v2/campaigns/%d/orders", campaignId)
	headers := map[string]string{"Api-Key": apiKey}
	data, err := y.client.DoRequest(ctx, http.MethodGet, path, nil, headers)
	if err != nil {
		return nil, err
	}
	var resp struct {
		Result struct {
			Orders []YandexOrder `json:"orders"`
		} `json:"result"`
	}
	json.Unmarshal(data, &resp)
	return resp.Result.Orders, nil
}
