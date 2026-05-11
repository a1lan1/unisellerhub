# Price Analyzer Service (Python)

This service provides data-driven pricing insights by analyzing sales history and current stock levels.

## 🚀 Key Features

*   **Sales Velocity Calculation:** Uses `Pandas` to calculate average daily sales.
*   **Inventory Forecasting:** Predicts "Days Left" until Out-of-Stock (OOS).
*   **Trend Analysis:** Identifies increasing, stable, or decreasing sales trends.

## 🛠 Tech Stack

*   **Language:** Python 3.11
*   **Libraries:** `pandas`, `pika`, `python-json-logger`.

## 📬 Queue Interface

### Input Task (`price.tasks`)
```json
{
  "organization_id": 1,
  "sku": "SKU-123",
  "current_stock": 50,
  "sales_history": [
    {"date": "2024-05-01", "quantity": 5},
    {"date": "2024-05-02", "quantity": 7}
  ]
}
```

### Output Result (`sync.results`)
```json
{
  "organization_id": 1,
  "operation": "price_analysis",
  "data": {
    "sku": "SKU-123",
    "stats": {
      "avg_daily_sales": 6.0,
      "days_left": 8,
      "trend": "increasing"
    }
  }
}
```
