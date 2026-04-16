<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Interfaces\Http\Requests\Ms;

use Illuminate\Foundation\Http\FormRequest;

class GetMsAssortmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // No specific rules for now
        ];
    }
}
