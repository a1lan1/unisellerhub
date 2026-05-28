<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Domain\Data;

use App\Modules\Marketplace\Domain\ValueObjects\Credentials;
use App\Modules\Marketplace\Domain\ValueObjects\MarketplaceConnectionName;

class UpdateMarketplaceConnectionData
{
    public function __construct(
        public ?MarketplaceConnectionName $name = null,
        public ?bool $isActive = null,
        public ?Credentials $credentials = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: isset($data['name']) ? new MarketplaceConnectionName((string) $data['name']) : null,
            isActive: isset($data['is_active']) ? (bool) $data['is_active'] : null,
            credentials: isset($data['credentials']) ? new Credentials((array) $data['credentials']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name?->getValue(),
            'is_active' => $this->isActive,
            'credentials' => $this->credentials?->getValue(),
        ], fn (string|bool|array|null $value): bool => $value !== null);
    }
}
