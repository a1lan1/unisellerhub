<?php

declare(strict_types=1);

namespace App\Modules\Product\Interfaces\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductFinanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'listing_id' => ['required', 'exists:product_listings,id'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'commission_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'logistic_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
