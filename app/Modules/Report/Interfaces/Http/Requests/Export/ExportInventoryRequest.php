<?php

declare(strict_types=1);

namespace App\Modules\Report\Interfaces\Http\Requests\Export;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Product\Domain\Data\ProductListingsFilterData;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;

class ExportInventoryRequest extends FormRequest
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
            'semanticSearch' => ['nullable', 'string', 'max:255'],
            'marketplace' => ['nullable', new Enum(MarketplaceEnum::class)],
            'sort' => ['nullable', 'string', 'in:vendor_code,name,marketplace'],
            'direction' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }

    public function toDto(): ProductListingsFilterData
    {
        return ProductListingsFilterData::from($this->validated());
    }
}
