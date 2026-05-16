<?php

declare(strict_types=1);

namespace App\Modules\Geo\Interfaces\Http\Requests;

use App\Modules\Geo\Domain\Data\ReviewData;
use App\Modules\Geo\Domain\Enums\ReviewSourceEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'location_id' => ['required', 'exists:locations,id'],
            'source' => ['required', 'string', Rule::enum(ReviewSourceEnum::class)],
            'external_id' => ['required', 'string'],
            'author_name' => ['required', 'string'],
            'published_at' => ['nullable', 'string'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'text' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function toDto(): ReviewData
    {
        return ReviewData::from($this->validated());
    }
}
