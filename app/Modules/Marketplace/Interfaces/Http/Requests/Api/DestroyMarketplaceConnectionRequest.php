<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Interfaces\Http\Requests\Api;

use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DestroyMarketplaceConnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var MarketplaceConnection $connection */
        $connection = $this->route('marketplace_connection');

        return $connection->organization_id === $this->user()->organization_id;
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
