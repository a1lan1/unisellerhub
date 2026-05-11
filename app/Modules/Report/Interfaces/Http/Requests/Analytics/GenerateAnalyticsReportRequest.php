<?php

declare(strict_types=1);

namespace App\Modules\Report\Interfaces\Http\Requests\Analytics;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'days' => ['nullable', 'integer', 'min:1'], // For product_revenue_analysis
        ];
    }

    public function toDto(): array
    {
        return [
            'reportType' => $this->input('report_type'),
            'reportParams' => [
                'days' => $this->input('days'),
            ],
        ];
    }
}
