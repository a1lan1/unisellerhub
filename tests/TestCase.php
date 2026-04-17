<?php

declare(strict_types=1);

namespace Tests;

use App\Modules\Shared\Application\Services\TenantManager;
use App\Modules\User\Domain\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Fortify\Features;
use Override;

abstract class TestCase extends BaseTestCase
{
    protected function skipUnlessFortifyHas(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? sprintf('Fortify feature [%s] is not enabled.', $feature));
        }
    }

    #[Override]
    public function actingAs(Authenticatable $user, $guard = null): static
    {
        if ($user instanceof User && $user->organization_id !== null) {
            resolve(TenantManager::class)->setOrganizationId($user->organization_id);
        }

        return parent::actingAs($user, $guard);
    }
}
