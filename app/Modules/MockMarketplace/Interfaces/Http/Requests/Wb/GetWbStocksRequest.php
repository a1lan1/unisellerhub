<?php

declare(strict_types=1);

namespace App\Modules\MockMarketplace\Interfaces\Http\Requests\Wb;

use Illuminate\Foundation\Http\FormRequest;

class GetWbStocksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'warehouseId' => ['nullable', 'integer'],
        ];
    }
}
