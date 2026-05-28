<?php

declare(strict_types=1);

namespace App\Modules\Report\Interfaces\Http\Requests\Export;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Order\Domain\Data\OrderFilterData;
use App\Modules\Order\Domain\Enums\OrderStatusEnum;
use App\Modules\Shared\Domain\ValueObjects\DateRange;
use App\Modules\Shared\Domain\ValueObjects\Pagination;
use App\Modules\Shared\Domain\ValueObjects\SortOrder;
use DateTimeImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;

class ExportOrdersRequest extends FormRequest
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
            'marketplace' => ['nullable', new Enum(MarketplaceEnum::class)],
            'statuses' => ['nullable', 'array'],
            'statuses.*' => ['required', new Enum(OrderStatusEnum::class)],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'sort' => ['nullable', 'string', 'in:order_date,total_price'],
            'direction' => ['nullable', 'string', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function toDto(): OrderFilterData
    {
        $validated = $this->validated();

        $perPage = (int) ($validated['per_page'] ?? 15);
        $page = (int) ($validated['page'] ?? 1);
        $pagination = new Pagination($perPage, $page);

        $dateFrom = $validated['date_from'] ?? null;
        $dateTo = $validated['date_to'] ?? null;
        $dateRange = ($dateFrom && $dateTo) ? new DateRange(new DateTimeImmutable($dateFrom), new DateTimeImmutable($dateTo)) : null;

        $sort = $validated['sort'] ?? null;
        $direction = $validated['direction'] ?? null;
        $sortOrder = ($sort && $direction) ? new SortOrder($sort, $direction) : null;

        return new OrderFilterData(
            pagination: $pagination,
            search: $validated['search'] ?? null,
            marketplace: isset($validated['marketplace']) ? MarketplaceEnum::from($validated['marketplace']) : null,
            statuses: isset($validated['statuses']) ? array_map(OrderStatusEnum::from(...), $validated['statuses']) : null,
            dateRange: $dateRange,
            sortOrder: $sortOrder,
        );
    }
}
