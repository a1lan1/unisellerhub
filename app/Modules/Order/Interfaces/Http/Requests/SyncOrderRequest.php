<?php

declare(strict_types=1);

namespace App\Modules\Order\Interfaces\Http\Requests;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class SyncOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'marketplace' => ['nullable', new Enum(MarketplaceEnum::class)],
        ];
    }
}
