<?php

declare(strict_types=1);

use App\Modules\Product\Domain\Models\Product;
use App\Modules\Shared\Application\Services\TenantManager;
use App\Modules\User\Domain\Enums\RoleEnum;
use App\Modules\User\Domain\Models\Organization;
use App\Modules\User\Domain\Models\User;
use App\Modules\User\Infrastructure\Repositories\EloquentUserRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

beforeEach(function (): void {
    $this->repository = new EloquentUserRepository;
    $this->organization = Organization::factory()->create();
    resolve(TenantManager::class)->setOrganizationId($this->organization->id);
});

it('finds a user by ID', function (): void {
    $user = User::factory()->create(['organization_id' => $this->organization->id]);
    $foundUser = $this->repository->findOrFail($user->id);
    expect($foundUser->id)->toBe($user->id);
    expect($foundUser->name)->toBe($user->name);
});

it('throws ModelNotFoundException if user not found by ID', function (): void {
    $this->repository->findOrFail(999);
})->throws(ModelNotFoundException::class);

it('finds a user by email', function (): void {
    $user = User::factory()->create(['organization_id' => $this->organization->id]);
    $foundUser = $this->repository->findByEmail($user->email);
    expect($foundUser->email)->toBe($user->email);
});

it('throws ModelNotFoundException if user not found by email', function (): void {
    $this->repository->findByEmail('nonexistent@example.com');
})->throws(ModelNotFoundException::class);

it('searches users by name or email', function (): void {
    User::factory()->create(['organization_id' => $this->organization->id, 'name' => 'John Doe', 'email' => 'john@example.com']);
    User::factory()->create(['organization_id' => $this->organization->id, 'name' => 'Jane Smith', 'email' => 'jane@test.com']);
    User::factory()->create(['organization_id' => $this->organization->id, 'name' => 'Peter Jones', 'email' => 'peter@example.com']);

    $results = $this->repository->searchByNameOrEmail('john');
    expect($results)->toHaveCount(1);
    expect($results->first()->name)->toBe('John Doe');

    $results = $this->repository->searchByNameOrEmail('example.com');
    expect($results)->toHaveCount(2);

    $results = $this->repository->searchByNameOrEmail('smith');
    expect($results)->toHaveCount(1);
    expect($results->first()->name)->toBe('Jane Smith');
});

it('excludes a specific user from search results', function (): void {
    $user1 = User::factory()->create(['organization_id' => $this->organization->id, 'name' => 'John Doe', 'email' => 'john@example.com']);
    User::factory()->create(['organization_id' => $this->organization->id, 'name' => 'John Smith', 'email' => 'john.smith@example.com']);

    $results = $this->repository->searchByNameOrEmail('john', excludeUserId: $user1->id);
    expect($results)->toHaveCount(1);
    expect($results->first()->name)->toBe('John Smith');
});

it('limits search results', function (): void {
    User::factory()->count(5)->create(['organization_id' => $this->organization->id, 'name' => 'Test User']);
    $results = $this->repository->searchByNameOrEmail('Test User', limit: 3);
    expect($results)->toHaveCount(3);
});

it('gets sellers', function (): void {
    User::factory()->count(2)->withBaseRoles()->create(['organization_id' => $this->organization->id]);
    User::factory()->count(3)->create(['organization_id' => $this->organization->id]); // These are not sellers

    $sellers = $this->repository->getSellers();
    expect($sellers)->toHaveCount(2);
    expect($sellers->first()->hasRole(RoleEnum::SELLER))->toBeTrue();
});

it('gets seller with products', function (): void {
    $seller = User::factory()->create(['organization_id' => $this->organization->id]);
    Product::factory()->count(5)->create(['organization_id' => $this->organization->id]);
    Product::factory()->count(5)->create(['organization_id' => $this->organization->id]); // 10 products total

    $sellerWithProducts = $this->repository->getSellerWithProducts($seller, 8);

    expect($sellerWithProducts->id)->toBe($seller->id);
    expect($sellerWithProducts->organization)->not->toBeNull();
    expect($sellerWithProducts->organization->products)->toHaveCount(8);
});
