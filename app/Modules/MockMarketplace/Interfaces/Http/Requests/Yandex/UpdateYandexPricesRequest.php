<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Interfaces\Http\Requests\Yandex;

use Illuminate\Foundation\Http\FormRequest;

class UpdateYandexPricesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'offers' => ['required', 'array'],
            'offers.*.offerId' => ['required', 'string'],
            'offers.*.price.value' => ['required', 'numeric', 'min:0'],
        ];
    }
}
