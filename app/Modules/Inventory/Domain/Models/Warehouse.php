<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Models;

use App\Modules\User\Domain\Models\Organization;
use App\Modules\User\Domain\Scopes\UserOrganizationScope;
use App\Observers\OrganizationIdObserver;
use Carbon\CarbonImmutable;
use Database\Factories\WarehouseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $organization_id
 * @property string $name
 * @property string|null $marketplace
 * @property string|null $external_id
 * @property string|null $address
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Organization $organization
 *
 * @method static WarehouseFactory factory($count = null, $state = [])
 * @method static Builder<static>|Warehouse newModelQuery()
 * @method static Builder<static>|Warehouse newQuery()
 * @method static Builder<static>|Warehouse query()
 * @method static Builder<static>|Warehouse whereAddress($value)
 * @method static Builder<static>|Warehouse whereCreatedAt($value)
 * @method static Builder<static>|Warehouse whereExternalId($value)
 * @method static Builder<static>|Warehouse whereId($value)
 * @method static Builder<static>|Warehouse whereMarketplace($value)
 * @method static Builder<static>|Warehouse whereName($value)
 * @method static Builder<static>|Warehouse whereOrganizationId($value)
 * @method static Builder<static>|Warehouse whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['name', 'marketplace', 'external_id', 'address', 'organization_id'])]
#[ScopedBy([UserOrganizationScope::class])]
#[ObservedBy([OrganizationIdObserver::class])]
#[UseFactory(WarehouseFactory::class)]
class Warehouse extends Model
{
    use HasFactory;

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
