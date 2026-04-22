<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Interfaces\Http\Requests\Api\Webhooks;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class OzonWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'string'],
            'message_type' => ['nullable', 'string'],
        ];
    }

    #[Override]
    public function validationData(): array
    {
        return array_merge(
            $this->all(),
            ['client_id' => $this->header('Client-Id')]
        );
    }
}
