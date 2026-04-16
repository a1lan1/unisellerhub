<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Interfaces\Http\Requests\Ozon;

use Illuminate\Foundation\Http\FormRequest;

class GetOzonStocksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['nullable', 'array'],
            'product_id.*' => ['integer'],
        ];
    }
}
