<?php

declare(strict_types=1);

namespace App\Modules\Geo\Infrastructure\Repositories;

use App\Modules\Geo\Domain\Data\ResponseTemplateData;
use App\Modules\Geo\Domain\Models\ResponseTemplate;
use App\Modules\Geo\Domain\Repositories\ResponseTemplateRepositoryInterface;
use App\Modules\User\Domain\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ResponseTemplateRepository implements ResponseTemplateRepositoryInterface
{
    public function getForUser(User $user): Collection
    {
        return $user->responseTemplates()
            ->select(['id', 'user_id', 'title', 'body', 'created_at'])
            ->latest()
            ->get();
    }

    public function store(ResponseTemplateData $data): ResponseTemplate
    {
        return ResponseTemplate::updateOrCreate(
            ['id' => $data->id],
            [
                'user_id' => $data->userId,
                'title' => $data->title,
                'body' => $data->body,
            ]
        );
    }

    public function delete(ResponseTemplate $template): void
    {
        $template->delete();
    }
}
