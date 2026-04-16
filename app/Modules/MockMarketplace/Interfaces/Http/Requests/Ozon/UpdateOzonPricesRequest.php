<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Interfaces\Http\Requests\Ozon;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOzonPricesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prices' => ['required', 'array'],
            'prices.*.offer_id' => ['required', 'string'],
            'prices.*.price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
