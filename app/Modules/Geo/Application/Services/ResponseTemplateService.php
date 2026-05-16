<?php

declare(strict_types=1);

namespace App\Modules\Geo\Application\Services;

use App\Modules\Geo\Domain\Data\ResponseTemplateData;
use App\Modules\Geo\Domain\Interfaces\ResponseTemplateServiceInterface;
use App\Modules\Geo\Domain\Models\ResponseTemplate;
use App\Modules\Geo\Domain\Repositories\ResponseTemplateRepositoryInterface;
use App\Modules\Shared\Domain\Enums\CacheKeyEnum;
use App\Modules\User\Domain\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;

class ResponseTemplateService implements ResponseTemplateServiceInterface
{
    public function __construct(protected ResponseTemplateRepositoryInterface $responseTemplateRepository) {}

    /**
     * @return Collection<int, ResponseTemplate>
     */
    public function getTemplatesForUser(User $user): Collection
    {
        return Cache::tags(['response_templates'])->flexible(
            sprintf(CacheKeyEnum::RESPONSE_TEMPLATES_FOR_USER->value, $user->id),
            [Date::now()->addMinutes(15), Date::now()->addHour()],
            fn (): Collection => $this->responseTemplateRepository->getForUser($user)
        );
    }

    public function storeTemplate(ResponseTemplateData $data): ResponseTemplate
    {
        return $this->responseTemplateRepository->store($data);
    }

    public function deleteTemplate(ResponseTemplate $template): void
    {
        $this->responseTemplateRepository->delete($template);
    }
}
