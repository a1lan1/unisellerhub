<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Interfaces\Http\Requests\Wb;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWbPricesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            '*' => ['required', 'array'],
            '*.nmId' => ['required', 'integer'],
            '*.price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
