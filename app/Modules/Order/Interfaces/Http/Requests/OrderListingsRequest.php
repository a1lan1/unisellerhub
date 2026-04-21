<?php

declare(strict_types=1);

namespace App\Modules\Order\Interfaces\Http\Requests;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Order\Domain\Data\OrderFilterData;
use App\Modules\Order\Domain\Enums\OrderStatusEnum;
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
        return OrderFilterData::from([
            'search' => $this->query('search'),
            'marketplace' => $this->query('marketplace'),
            'statuses' => $this->query('statuses'),
            'date_from' => $this->query('date_from'),
            'date_to' => $this->query('date_to'),
            'sort' => $this->query('sort'),
            'direction' => $this->query('direction'),
            'per_page' => (int) $this->query('per_page', 15),
            'page' => (int) $this->query('page', 1),
        ]);
    }
}
