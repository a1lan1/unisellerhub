<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Interfaces\Http\Requests\Api;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PullInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'marketplace' => ['nullable', 'string', Rule::enum(MarketplaceEnum::class)],
        ];
    }
}
