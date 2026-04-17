<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Interfaces\Http\Requests\Api\Webhooks;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class MoySkladWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'Authorization' => ['required', 'string', 'starts_with:Bearer '],
            'events' => ['required', 'array'],
        ];
    }

    #[Override]
    public function validationData(): array
    {
        return array_merge(
            $this->all(),
            ['Authorization' => $this->header('Authorization')]
        );
    }
}
