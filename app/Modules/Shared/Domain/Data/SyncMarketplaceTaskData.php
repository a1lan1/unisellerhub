<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\Data;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\Marketplace\Domain\Enums\SyncOperationTypeEnum;
use Override;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class SyncMarketplaceTaskData extends Data
{
    public function __construct(
        public int $organizationId,
        public ?string $id = null,
        public ?string $displayName = null,
        public ?MarketplaceEnum $marketplace = null,
        public ?SyncOperationTypeEnum $operation = null,
        public array $payload = [],
    ) {}

    #[Override]
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'displayName' => $this->displayName,
            'organization_id' => $this->organizationId,
            'marketplace' => $this->marketplace->value,
            'operation' => $this->operation->value,
            'payload' => $this->payload,
        ];
    }

    public function toJsonEncode(): string
    {
        return json_encode(
            $this->toArray()
        );
    }
}
