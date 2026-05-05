# Report Generator Service (Python)

High-speed service for generating analytical reports in Excel format.

## 🚀 Key Features

*   **Excel Generation:** Uses `Pandas` and `XlsxWriter` for structured and formatted reports.
*   **Performance:** Offloads heavy computation and file generation from the main PHP application.
*   **JSON to Excel:** Transforms raw JSON data arrays into professional spreadsheets.

## 🛠 Tech Stack

*   **Language:** Python 3.11
*   **Libraries:** `pandas`, `xlsxwriter`, `pika`.

## 📬 Queue Interface

### Input Task (`report.tasks`)
```json
{
  "organization_id": 1,
  "report_type": "inventory_audit",
  "data": [
    {"SKU": "A1", "Stock": 10},
    {"SKU": "B2", "Stock": 5}
  ]
}
```

### Output Result (`sync.results`)
```json
{
  "organization_id": 1,
  "operation": "report_generation",
  "status": "success",
  "data": {
    "filename": "report_1_1716200000.xlsx",
    "download_url": "/storage/reports/report_1_1716200000.xlsx"
  }
}
```

## 🛠 Local Development

```bash
docker compose up report_generator --build
```
