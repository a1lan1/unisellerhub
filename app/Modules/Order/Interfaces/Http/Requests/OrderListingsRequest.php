<?php

declare(strict_types=1);

namespace App\Modules\Order\Interfaces\Http\Requests;

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
use Illuminate\Validation\Rule;

class OrderListingsRequest extends FormRequest
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
            'statuses' => ['nullable', 'array'],
            'statuses.*' => ['string', Rule::enum(OrderStatusEnum::class)],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'sort' => ['nullable', 'string', Rule::in(['external_id', 'status', 'total_price', 'order_date'])],
            'direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function toDto(): OrderFilterData
    {
        $perPage = (int) $this->query('per_page', 15);
        $page = (int) $this->query('page', 1);
        $pagination = new Pagination($perPage, $page);

        $dateFrom = $this->query('date_from');
        $dateTo = $this->query('date_to');
        $dateRange = ($dateFrom && $dateTo) ? new DateRange(new DateTimeImmutable($dateFrom), new DateTimeImmutable($dateTo)) : null;

        $sort = $this->query('sort');
        $direction = $this->query('direction');
        $sortOrder = ($sort && $direction) ? new SortOrder($sort, $direction) : null;

        return new OrderFilterData(
            pagination: $pagination,
            search: $this->query('search'),
            marketplace: $this->query('marketplace') ? MarketplaceEnum::from($this->query('marketplace')) : null,
            statuses: $this->query('statuses') ? array_map(OrderStatusEnum::from(...), $this->query('statuses')) : null,
            dateRange: $dateRange,
            sortOrder: $sortOrder,
        );
    }
}
