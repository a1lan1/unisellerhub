<?php

declare(strict_types=1);

use App\Modules\Geo\Domain\Data\ResponseTemplateData;
use App\Modules\Geo\Domain\Models\ResponseTemplate;
use App\Modules\Geo\Infrastructure\Repositories\ResponseTemplateRepository;
use App\Modules\User\Domain\Models\User;

beforeEach(function (): void {
    $this->repository = new ResponseTemplateRepository;
    $this->user = User::factory()->withBaseRoles()->create();
});

it('gets response templates for a user', function (): void {
    ResponseTemplate::factory()->count(3)->create(['user_id' => $this->user->id]);
    ResponseTemplate::factory()->count(2)->create(); // Other user's templates

    $templates = $this->repository->getForUser($this->user);

    expect($templates)->toHaveCount(3);
    expect($templates->first())->toBeInstanceOf(ResponseTemplate::class);
    expect($templates->first()->user_id)->toBe($this->user->id);
});

it('stores a new response template', function (): void {
    $templateData = ResponseTemplateData::from([
        'userId' => $this->user->id,
        'title' => 'New Template',
        'body' => 'Hello, {name}!',
    ]);

    $template = $this->repository->store($templateData);

    expect($template->title)->toBe('New Template');
    expect($template->body)->toBe('Hello, {name}!');
    $this->assertDatabaseHas('response_templates', ['title' => 'New Template']);
});

it('updates an existing response template', function (): void {
    $existingTemplate = ResponseTemplate::factory()->create(['user_id' => $this->user->id, 'title' => 'Old Title']);
    $templateData = ResponseTemplateData::from([
        'id' => $existingTemplate->id,
        'userId' => $this->user->id,
        'title' => 'Updated Title',
        'body' => 'Updated body.',
    ]);

    $template = $this->repository->store($templateData);

    expect($template->id)->toBe($existingTemplate->id);
    expect($template->title)->toBe('Updated Title');
    expect($template->body)->toBe('Updated body.');
    $this->assertDatabaseHas('response_templates', ['id' => $existingTemplate->id, 'title' => 'Updated Title']);
});

it('deletes a response template', function (): void {
    $template = ResponseTemplate::factory()->create(['user_id' => $this->user->id]);

    $this->repository->delete($template);

    $this->assertDatabaseMissing('response_templates', ['id' => $template->id]);
});
