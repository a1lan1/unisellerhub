<?php

declare(strict_types=1);

namespace App\Modules\Report\Interfaces\Http\Requests\Analytics;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

class GenerateAnalyticsReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'report_type' => ['required', 'string', Rule::in(['product_revenue_analysis', 'product_profitability_analysis'])],
            'days' => ['nullable', 'integer', 'min:1'],
            'endDate' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    #[Override]
    protected function prepareForValidation(): void
    {
        $this->merge([
            'days' => $this->integer('days', 30),
            'endDate' => $this->input('endDate', now()->format('Y-m-d')),
        ]);
    }

    public function toDto(): array
    {
        return [
            'reportType' => $this->input('report_type'),
            'reportParams' => [
                'days' => $this->integer('days', 30),
                'endDate' => $this->input('endDate', now()->format('Y-m-d')),
            ],
        ];
    }
}
