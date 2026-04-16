<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Domain\Models;

use App\Modules\Marketplace\Domain\Enums\MarketplaceEnum;
use App\Modules\User\Domain\Models\Organization;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int $organization_id
 * @property MarketplaceEnum $marketplace
 * @property string $name
 * @property array<array-key, mixed> $credentials
 * @property bool $is_active
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Organization $organization
 *
 * @method static Builder<static>|MarketplaceConnection newModelQuery()
 * @method static Builder<static>|MarketplaceConnection newQuery()
 * @method static Builder<static>|MarketplaceConnection query()
 * @method static Builder<static>|MarketplaceConnection whereCreatedAt($value)
 * @method static Builder<static>|MarketplaceConnection whereCredentials($value)
 * @method static Builder<static>|MarketplaceConnection whereId($value)
 * @method static Builder<static>|MarketplaceConnection whereIsActive($value)
 * @method static Builder<static>|MarketplaceConnection whereMarketplace($value)
 * @method static Builder<static>|MarketplaceConnection whereName($value)
 * @method static Builder<static>|MarketplaceConnection whereOrganizationId($value)
 * @method static Builder<static>|MarketplaceConnection whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['organization_id', 'marketplace', 'name', 'credentials', 'is_active'])]
class MarketplaceConnection extends Model
{
    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'credentials' => 'encrypted:array',
            'marketplace' => MarketplaceEnum::class,
            'is_active' => 'boolean',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
