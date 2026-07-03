<?php

declare(strict_types=1);

use App\Modules\Geo\Application\Services\ResponseTemplateService;
use App\Modules\Geo\Domain\Data\ResponseTemplateData;
use App\Modules\Geo\Domain\Models\ResponseTemplate;
use App\Modules\Geo\Domain\Repositories\ResponseTemplateRepositoryInterface;
use App\Modules\Shared\Domain\Enums\CacheKeyEnum;
use App\Modules\User\Domain\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    $this->responseTemplateRepository = $this->mock(ResponseTemplateRepositoryInterface::class);
    $this->responseTemplateService = new ResponseTemplateService($this->responseTemplateRepository);
    Cache::flush();
});

it('gets response templates for a user from cache', function (): void {
    $user = User::factory()->withBaseRoles()->create();
    $templates = new Collection([ResponseTemplate::factory()->make()]);
    $cacheKey = sprintf(CacheKeyEnum::RESPONSE_TEMPLATES_FOR_USER->value, $user->id);

    Cache::shouldReceive('tags')
        ->with(['response_templates'])
        ->andReturnSelf();
    Cache::shouldReceive('remember')
        ->with($cacheKey, Mockery::type(CarbonImmutable::class), Mockery::type(Closure::class))
        ->andReturn($templates);

    $result = $this->responseTemplateService->getTemplatesForUser($user);

    expect($result)->toEqual($templates);
});

it('gets response templates for a user from repository if not in cache', function (): void {
    $user = User::factory()->withBaseRoles()->create();
    $templates = new Collection([ResponseTemplate::factory()->make()]);
    $cacheKey = sprintf(CacheKeyEnum::RESPONSE_TEMPLATES_FOR_USER->value, $user->id);

    Cache::shouldReceive('tags')
        ->with(['response_templates'])
        ->andReturnSelf();
    Cache::shouldReceive('remember')
        ->with($cacheKey, Mockery::type(CarbonImmutable::class), Mockery::type(Closure::class))
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    $this->responseTemplateRepository->shouldReceive('getForUser')
        ->once()
        ->with($user)
        ->andReturn($templates);

    $result = $this->responseTemplateService->getTemplatesForUser($user);

    expect($result)->toEqual($templates);
});

it('stores a response template', function (): void {
    $user = User::factory()->withBaseRoles()->create();
    $templateData = ResponseTemplateData::from([
        'userId' => $user->id,
        'title' => 'Test Template',
        'body' => 'Hello {name}',
    ]);
    $template = ResponseTemplate::factory()->make();

    $this->responseTemplateRepository->shouldReceive('store')
        ->once()
        ->with($templateData)
        ->andReturn($template);

    $result = $this->responseTemplateService->storeTemplate($templateData);

    expect($result)->toEqual($template);
});

it('deletes a response template', function (): void {
    $template = ResponseTemplate::factory()->create();

    $this->responseTemplateRepository->shouldReceive('delete')
        ->once()
        ->with($template);

    $this->responseTemplateService->deleteTemplate($template);

    // No assertion needed as it's a void method, we just check if the mock was called
    expect(true)->toBeTrue();
});
