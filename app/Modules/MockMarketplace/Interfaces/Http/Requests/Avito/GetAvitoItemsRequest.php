<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Interfaces\Http\Requests\Avito;

use Illuminate\Foundation\Http\FormRequest;

class GetAvitoItemsRequest extends FormRequest
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
