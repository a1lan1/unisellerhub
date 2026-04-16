<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Interfaces\Http\Requests\Ozon;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOzonStocksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'stocks' => ['required', 'array'],
            'stocks.*.offer_id' => ['required', 'string'],
            'stocks.*.stock' => ['required', 'integer', 'min:0'],
        ];
    }
}
