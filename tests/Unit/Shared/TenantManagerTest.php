<?php

declare(strict_types=1);

use App\Modules\Shared\Application\Services\TenantManager;

beforeEach(function (): void {
    $this->tenantManager = new TenantManager;
});

it('can set and get organization ID', function (): void {
    $organizationId = 123;
    $this->tenantManager->setOrganizationId($organizationId);
    expect($this->tenantManager->getOrganizationId())->toBe($organizationId);
});

it('returns null if organization ID is not set', function (): void {
    expect($this->tenantManager->getOrganizationId())->toBeNull();
});

it('can clear the organization ID', function (): void {
    $this->tenantManager->setOrganizationId(123);
    $this->tenantManager->clear();

    expect($this->tenantManager->getOrganizationId())->toBeNull();
});
