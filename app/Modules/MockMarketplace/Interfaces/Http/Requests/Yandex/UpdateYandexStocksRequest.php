<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Interfaces\Http\Requests\Yandex;

use Illuminate\Foundation\Http\FormRequest;

class UpdateYandexStocksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'skus' => ['nullable', 'array'],
            'skus.*.sku' => ['required', 'string'],
            'skus.*.warehouseStocks' => ['nullable', 'array'],
            'skus.*.warehouseStocks.*.count' => ['required', 'integer', 'min:0'],
        ];
    }
}
