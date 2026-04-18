<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Interfaces\Http\Requests\Api;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMarketplaceConnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'marketplace' => ['required', Rule::enum(MarketplaceEnum::class)],
            'name' => ['required', 'string', 'max:255'],
            'credentials' => ['required', 'array'],

            // WB
            'credentials.token' => [Rule::requiredIf(fn (): bool => $this->input('marketplace') === MarketplaceEnum::WB->value), 'string'],

            // Ozon
            'credentials.client_id' => [Rule::requiredIf(fn (): bool => $this->input('marketplace') === MarketplaceEnum::OZON->value || $this->input('marketplace') === MarketplaceEnum::AVITO->value), 'string'],
            'credentials.api_key' => [Rule::requiredIf(fn (): bool => $this->input('marketplace') === MarketplaceEnum::OZON->value || $this->input('marketplace') === MarketplaceEnum::YANDEX->value), 'string'],

            // MoySklad
            'credentials.ms_token' => [Rule::requiredIf(fn (): bool => $this->input('marketplace') === MarketplaceEnum::MOYSKLAD->value), 'string'],

            // Yandex
            'credentials.campaign_id' => [Rule::requiredIf(fn (): bool => $this->input('marketplace') === MarketplaceEnum::YANDEX->value), 'string'],
            'credentials.business_id' => [Rule::requiredIf(fn (): bool => $this->input('marketplace') === MarketplaceEnum::YANDEX->value), 'string'],

            // Avito
            'credentials.client_secret' => [Rule::requiredIf(fn (): bool => $this->input('marketplace') === MarketplaceEnum::AVITO->value), 'string'],
        ];
    }
}
