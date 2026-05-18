<?php

declare(strict_types=1);

namespace App\Modules\Report\Interfaces\Http\Requests\Analytics;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Override;

class AbcAnalysisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('has-organization');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'endDate' => ['sometimes', 'date_format:Y-m-d'],
            'days' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    #[Override]
    protected function prepareForValidation(): void
    {
        $this->merge([
            'endDate' => $this->input('endDate', now()->format('Y-m-d')),
            'days' => $this->integer('days', 30),
        ]);
    }
}
