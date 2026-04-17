<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Interfaces\Http\Requests\Api\Webhooks;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
}
