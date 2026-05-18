<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Domain\Data;

class UpdateMarketplaceConnectionData
{
    public function __construct(
        public ?string $name = null,
        public ?bool $isActive = null,
        public ?array $credentials = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: isset($data['name']) ? (string) $data['name'] : null,
            isActive: isset($data['is_active']) ? (bool) $data['is_active'] : null,
            credentials: isset($data['credentials']) ? (array) $data['credentials'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'is_active' => $this->isActive,
            'credentials' => $this->credentials,
        ], fn (string|bool|array|null $value): bool => $value !== null);
    }
}
