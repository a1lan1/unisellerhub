<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Interfaces\Http\Requests\Api\Webhooks;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class WildberriesWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'Authorization' => ['required', 'string'],
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
