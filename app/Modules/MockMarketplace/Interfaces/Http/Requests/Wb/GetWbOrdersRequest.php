<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Interfaces\Http\Requests\Wb;

use Illuminate\Foundation\Http\FormRequest;

class GetWbOrdersRequest extends FormRequest
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
