<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Interfaces;

use App\Modules\Geo\Domain\Data\ResponseTemplateData;
use App\Modules\Geo\Domain\Models\ResponseTemplate;
use App\Modules\User\Domain\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface ResponseTemplateServiceInterface
{
    /**
     * @return Collection<int, ResponseTemplate>
     */
    public function getTemplatesForUser(User $user): Collection;

    public function storeTemplate(ResponseTemplateData $data): ResponseTemplate;

    public function deleteTemplate(ResponseTemplate $template): void;
}
