<?php

declare(strict_types=1);

namespace App\Modules\Product\Interfaces\Http\Requests\Api;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class SyncProductRequest extends FormRequest
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
