<?php

declare(strict_types=1);

namespace App\Modules\Product\Interfaces\Http\Requests;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Product\Domain\Data\ProductListingsFilterData;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ProductListingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('has-organization');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'marketplace' => ['nullable', 'string', Rule::enum(MarketplaceEnum::class)],
            'sort' => ['nullable', 'string', Rule::in(['name', 'sku', 'price', 'quantity'])],
            'direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function toDto(): ProductListingsFilterData
    {
        return ProductListingsFilterData::from([
            'search' => $this->query('search'),
            'marketplace' => $this->query('marketplace'),
            'sort' => $this->query('sort'),
            'direction' => $this->query('direction'),
            'per_page' => (int) $this->query('per_page', 15),
            'page' => (int) $this->query('page', 1),
        ]);
    }
}
