package marketplace

import (
	"context"
	"encoding/json"
	"net/http"
	"strings"
)

type OzonClient struct {
	client *Client
}

func NewOzonClient(baseURL, apiKey string) *OzonClient {
	return &OzonClient{
		client: NewClient(baseURL, apiKey, 20),
	}
}

type OzonStockItem struct {
	Type    string `json:"type"`
	Present int    `json:"present"`
}

type OzonStock struct {
	ProductID int             `json:"productId"`
	OfferID   string          `json:"offerId"`
	Stocks    []OzonStockItem `json:"stocks"`
}

type OzonProduct struct {
	ProductID int    `json:"productId"`
	OfferID   string `json:"offerId"`
	Name      string `json:"name"`
	Price     string `json:"price"`
}

type OzonOrderItem struct {
	Sku      int    `json:"sku"`
	OfferID  string `json:"offerId"`
	Quantity int    `json:"quantity"`
	Price    string `json:"price"`
}

type OzonOrder struct {
	PostingNumber string          `json:"postingNumber"`
	Status        string          `json:"status"`
	InProcessAt   string          `json:"inProcessAt"`
	Products      []OzonOrderItem `json:"products"`
}

func (o *OzonClient) GetStocks(ctx context.Context, clientId, apiKey string) ([]OzonStock, error) {
	headers := map[string]string{"Client-Id": clientId, "Api-Key": apiKey}
	data, err := o.client.DoRequest(ctx, http.MethodPost, "/v1/product/list", strings.NewReader(`{}`), headers)
	if err != nil {
		return nil, err
	}
	var listResp struct {
		Result struct {
			Items []struct {
				ProductID int `json:"product_id"`
			} `json:"items"`
		} `json:"result"`
	}
	json.Unmarshal(data, &listResp)
	productIDs := make([]int, 0)
	for _, item := range listResp.Result.Items {
		productIDs = append(productIDs, item.ProductID)
	}
	payload, _ := json.Marshal(map[string]any{"product_id": productIDs})
	data, err = o.client.DoRequest(ctx, http.MethodPost, "/v1/product/info/stocks", strings.NewReader(string(payload)), headers)
	if err != nil {
		return nil, err
	}
	var resp struct {
		Result struct {
			Items []struct {
				ProductID int    `json:"product_id"`
				OfferID   string `json:"offer_id"`
				Stocks    []struct {
					Type    string `json:"type"`
					Present int    `json:"present"`
				} `json:"stocks"`
			} `json:"items"`
		} `json:"result"`
	}
	json.Unmarshal(data, &resp)

	stocks := make([]OzonStock, 0)
	for _, item := range resp.Result.Items {
		s := OzonStock{ProductID: item.ProductID, OfferID: item.OfferID}
		for _, stock := range item.Stocks {
			s.Stocks = append(s.Stocks, OzonStockItem{
				Type:    stock.Type,
				Present: stock.Present,
			})
		}
		stocks = append(stocks, s)
	}
	return stocks, nil
}

func (o *OzonClient) GetProducts(ctx context.Context, clientId, apiKey string) ([]OzonProduct, error) {
	headers := map[string]string{"Client-Id": clientId, "Api-Key": apiKey}
	data, err := o.client.DoRequest(ctx, http.MethodPost, "/v1/product/list", strings.NewReader(`{}`), headers)
	if err != nil {
		return nil, err
	}
	var listResp struct {
		Result struct {
			Items []struct {
				ProductID int `json:"product_id"`
			} `json:"items"`
		} `json:"result"`
	}
	json.Unmarshal(data, &listResp)
	productIDs := make([]int, 0)
	for _, item := range listResp.Result.Items {
		productIDs = append(productIDs, item.ProductID)
	}
	detailsPayload, _ := json.Marshal(map[string]any{"product_id": productIDs})
	detailsData, err := o.client.DoRequest(ctx, http.MethodPost, "/v1/product/info/list", strings.NewReader(string(detailsPayload)), headers)
	if err != nil {
		return nil, err
	}
	var detailsResp struct {
		Result struct {
			Items []struct {
				ID      int    `json:"id"`
				OfferID string `json:"offer_id"`
				Name    string `json:"name"`
				Price   string `json:"price"`
			} `json:"items"`
		} `json:"result"`
	}
	json.Unmarshal(detailsData, &detailsResp)

	products := make([]OzonProduct, 0)
	for _, item := range detailsResp.Result.Items {
		products = append(products, OzonProduct{
			ProductID: item.ID,
			OfferID:   item.OfferID,
			Name:      item.Name,
			Price:     item.Price,
		})
	}
	return products, nil
}

func (o *OzonClient) GetOrders(ctx context.Context, clientId, apiKey string) ([]OzonOrder, error) {
	headers := map[string]string{"Client-Id": clientId, "Api-Key": apiKey}
	data, err := o.client.DoRequest(ctx, http.MethodPost, "/v1/posting/fbs/list", strings.NewReader(`{}`), headers)
	if err != nil {
		return nil, err
	}
	var resp struct {
		Result struct {
			Postings []struct {
				PostingNumber string `json:"posting_number"`
				Status        string `json:"status"`
				InProcessAt   string `json:"in_process_at"`
				Products      []struct {
					Sku      int    `json:"sku"`
					OfferID  string `json:"offer_id"`
					Quantity int    `json:"quantity"`
					Price    string `json:"price"`
				} `json:"products"`
			} `json:"postings"`
		} `json:"result"`
	}
	json.Unmarshal(data, &resp)

	orders := make([]OzonOrder, 0)
	for _, p := range resp.Result.Postings {
		order := OzonOrder{
			PostingNumber: p.PostingNumber,
			Status:        p.Status,
			InProcessAt:   p.InProcessAt,
		}
		for _, prod := range p.Products {
			order.Products = append(order.Products, OzonOrderItem{
				Sku: prod.Sku, OfferID: prod.OfferID, Quantity: prod.Quantity, Price: prod.Price,
			})
		}
		orders = append(orders, order)
	}
	return orders, nil
}
