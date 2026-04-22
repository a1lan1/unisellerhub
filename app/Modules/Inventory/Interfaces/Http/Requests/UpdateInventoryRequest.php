<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Interfaces\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'exists:inventory,id'],
            'quantity' => ['required', 'integer', 'min:0'],
        ];
    }
}
