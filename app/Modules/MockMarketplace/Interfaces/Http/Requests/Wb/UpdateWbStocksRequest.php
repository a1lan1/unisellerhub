<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Interfaces\Http\Requests\Wb;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWbStocksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'stocks' => ['required', 'array'],
            'stocks.*.sku' => ['required', 'string'],
            'stocks.*.amount' => ['required', 'integer', 'min:0'],
        ];
    }
}
