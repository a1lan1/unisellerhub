package marketplace

import (
	"context"
	"encoding/json"
	"net/http"
)

type MoySkladClient struct {
	client *Client
}

func NewMoySkladClient(baseURL, apiKey string) *MoySkladClient {
	return &MoySkladClient{
		client: NewClient(baseURL, apiKey, 2),
	}
}

type MSStock struct {
	Article string `json:"article"`
	Stock float64 `json:"stock"`
	Reserve float64 `json:"reserve"`
}

type MSProduct struct {
	ID string `json:"id"`
	Name string `json:"name"`
	Code string `json:"code"`
	Article string `json:"article"`
	SalePrices []struct {
		Value float64 `json:"value"`
	} `json:"salePrices"`
}

type MSOrder struct {
	ID string `json:"id"`
	Name string `json:"name"`
	Moment string `json:"moment"`
	Sum int `json:"sum"`
	State struct {
		Name string `json:"name"`
	} `json:"state"`
}

func (m *MoySkladClient) GetStocks(ctx context.Context, token string) ([]MSStock, error) {
	headers := map[string]string{"Authorization": "Bearer " + token}
	data, err := m.client.DoRequest(ctx, http.MethodGet, "/api/remap/1.2/report/stock/all", nil, headers)
	if err != nil {
		return nil, err
	}
	var resp struct {
		Rows []MSStock `json:"rows"`
	}
	json.Unmarshal(data, &resp)
	return resp.Rows, nil
}

func (m *MoySkladClient) GetProducts(ctx context.Context, token string) ([]MSProduct, error) {
	headers := map[string]string{"Authorization": "Bearer " + token}
	data, err := m.client.DoRequest(ctx, http.MethodGet, "/api/remap/1.2/entity/assortment", nil, headers)
	if err != nil {
		return nil, err
	}
	var resp struct {
		Rows []MSProduct `json:"rows"`
	}
	json.Unmarshal(data, &resp)
	return resp.Rows, nil
}

func (m *MoySkladClient) GetOrders(ctx context.Context, token string) ([]MSOrder, error) {
	headers := map[string]string{"Authorization": "Bearer " + token}
	data, err := m.client.DoRequest(ctx, http.MethodGet, "/api/remap/1.2/entity/customerorder", nil, headers)
	if err != nil {
		return nil, err
	}
	var resp struct {
		Rows []MSOrder `json:"rows"`
	}
	json.Unmarshal(data, &resp)
	return resp.Rows, nil
}
