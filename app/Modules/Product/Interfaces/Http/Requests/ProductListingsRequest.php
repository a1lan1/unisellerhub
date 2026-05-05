<?php

declare(strict_types=1);

namespace App\Modules\Product\Interfaces\Http\Requests;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Product\Domain\Data\ProductListingsFilterData;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Override;

class ProductListingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('has-organization');
    }

    /**
     * Prepare the data for validation.
     */
    #[Override]
    protected function prepareForValidation(): void
    {
        // Ensure only one search type is active to prevent confusion
        if ($this->filled('semantic_query') && $this->filled('search')) {
            $this->offsetUnset('search'); // Prioritize semantic search if both are present
        } elseif ($this->filled('search') && $this->filled('semantic_query')) {
            $this->offsetUnset('semantic_query'); // Prioritize traditional search if both are present
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'semantic_query' => ['nullable', 'string', 'max:255'],
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
            'semanticSearch' => $this->query('semantic_query'),
            'marketplace' => $this->query('marketplace'),
            'sort' => $this->query('sort'),
            'direction' => $this->query('direction'),
            'per_page' => (int) $this->query('per_page', 15),
            'page' => (int) $this->query('page', 1),
        ]);
    }
}
