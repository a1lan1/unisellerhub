<?php

declare(strict_types=1);

namespace App\Modules\Report\Interfaces\Http\Requests\Export;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Order\Domain\Data\OrderFilterData;
use App\Modules\Order\Domain\Enums\OrderStatusEnum;
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
        ];
    }

    public function toDto(): OrderFilterData
    {
        return OrderFilterData::from($this->validated());
    }
}
