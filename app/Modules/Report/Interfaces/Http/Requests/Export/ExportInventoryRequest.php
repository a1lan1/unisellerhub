<?php

declare(strict_types=1);

namespace App\Modules\Report\Interfaces\Http\Requests\Export;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Product\Domain\Data\ProductListingsFilterData;
use App\Modules\Shared\Domain\ValueObjects\Pagination;
use App\Modules\Shared\Domain\ValueObjects\SortOrder;
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
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function toDto(): ProductListingsFilterData
    {
        $validated = $this->validated();

        $perPage = (int) ($validated['per_page'] ?? 15);
        $page = (int) ($validated['page'] ?? 1);
        $pagination = new Pagination($perPage, $page);

        $sort = $validated['sort'] ?? null;
        $direction = $validated['direction'] ?? null;
        $sortOrder = ($sort && $direction) ? new SortOrder($sort, $direction) : null;

        return new ProductListingsFilterData(
            search: $validated['search'] ?? null,
            semanticSearch: $validated['semanticSearch'] ?? null,
            marketplace: isset($validated['marketplace']) ? MarketplaceEnum::from($validated['marketplace']) : null,
            sortOrder: $sortOrder,
            pagination: $pagination,
        );
    }
}
