<?php

declare(strict_types=1);

namespace App\Modules\Report\Interfaces\Http\Requests\Analytics;

use App\Modules\Report\Domain\Data\GenerateAnalyticsReportData;
use App\Modules\Report\Domain\Enums\ReportTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
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
            'report_type' => ['required', new Enum(ReportTypeEnum::class)],
            'days' => ['nullable', 'integer', 'min:1'],
            'end_date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    #[Override]
    protected function prepareForValidation(): void
    {
        $this->merge([
            'days' => $this->integer('days', 30),
            'end_date' => $this->input('end_date', now()->format('Y-m-d')),
        ]);
    }

    public function toDto(): GenerateAnalyticsReportData
    {
        $validated = $this->validated();

        return new GenerateAnalyticsReportData(
            reportType: ReportTypeEnum::from($validated['report_type']),
            days: (int) $validated['days'],
            endDate: (string) $validated['end_date'],
        );
    }
}
