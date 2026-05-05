<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Interfaces\Http\Requests;

use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BaseMarketplaceConnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var MarketplaceConnection|null $marketplaceConnection */
        $marketplaceConnection = $this->route('marketplaceConnection');

        if (! $marketplaceConnection instanceof MarketplaceConnection) {
            return false;
        }

        return $marketplaceConnection->organization_id === $this->user()->organization_id;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}
