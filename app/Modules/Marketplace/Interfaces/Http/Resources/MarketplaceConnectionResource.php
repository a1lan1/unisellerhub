<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Interfaces\Http\Resources;

use App\Modules\Marketplace\Domain\Models\MarketplaceConnection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin MarketplaceConnection
 */
class MarketplaceConnectionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'marketplace' => $this->marketplace->value,
            'name' => $this->name,
            'is_active' => $this->is_active,
            'credentials' => $this->getMaskedCredentials(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    private function getMaskedCredentials(): array
    {
        $credentials = $this->credentials;
        foreach ($credentials as $key => $value) {
            $credentials[$key] = strlen((string) $value) > 8
                ? substr((string) $value, 0, 4).'...'.substr((string) $value, -4)
                : '****';
        }

        return $credentials;
    }
}
