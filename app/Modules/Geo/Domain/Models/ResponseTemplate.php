<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Models;

use App\Modules\Geo\Domain\Policies\ResponseTemplatePolicy;
use App\Modules\User\Domain\Models\User;
use Carbon\CarbonImmutable;
use Database\Factories\ResponseTemplateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $body
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read User $seller
 *
 * @method static ResponseTemplateFactory factory($count = null, $state = [])
 * @method static Builder<static>|ResponseTemplate newModelQuery()
 * @method static Builder<static>|ResponseTemplate newQuery()
 * @method static Builder<static>|ResponseTemplate query()
 * @method static Builder<static>|ResponseTemplate whereBody($value)
 * @method static Builder<static>|ResponseTemplate whereCreatedAt($value)
 * @method static Builder<static>|ResponseTemplate whereId($value)
 * @method static Builder<static>|ResponseTemplate whereTitle($value)
 * @method static Builder<static>|ResponseTemplate whereUpdatedAt($value)
 * @method static Builder<static>|ResponseTemplate whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[UsePolicy(ResponseTemplatePolicy::class)]
#[Fillable([
    'user_id',
    'title',
    'body',
])]
#[UseFactory(ResponseTemplateFactory::class)]
class ResponseTemplate extends Model
{
    use HasFactory;

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
