<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Domain\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $mock_marketplace_account_id
 * @property string $key
 * @property string $value
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read MockMarketplaceAccount $mockMarketplaceAccount
 *
 * @method static Builder<static>|MockMarketplaceCredential newModelQuery()
 * @method static Builder<static>|MockMarketplaceCredential newQuery()
 * @method static Builder<static>|MockMarketplaceCredential query()
 * @method static Builder<static>|MockMarketplaceCredential whereCreatedAt($value)
 * @method static Builder<static>|MockMarketplaceCredential whereId($value)
 * @method static Builder<static>|MockMarketplaceCredential whereKey($value)
 * @method static Builder<static>|MockMarketplaceCredential whereMockMarketplaceAccountId($value)
 * @method static Builder<static>|MockMarketplaceCredential whereUpdatedAt($value)
 * @method static Builder<static>|MockMarketplaceCredential whereValue($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['mock_marketplace_account_id', 'key', 'value'])]
class MockMarketplaceCredential extends Model
{
    use HasFactory;

    public function mockMarketplaceAccount(): BelongsTo
    {
        return $this->belongsTo(MockMarketplaceAccount::class);
    }
}
