<?php

declare(strict_types=1);

namespace App\Modules\Geo\Interfaces\Http\Requests;

use App\Modules\Geo\Domain\Data\ResponseTemplateData;
use Illuminate\Foundation\Http\FormRequest;

class ResponseTemplateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ];
    }

    public function toDto(): ResponseTemplateData
    {
        if ($template = $this->route('response_template')) {
            return ResponseTemplateData::from([
                ...$template->toArray(),
                ...$this->validated(),
                'id' => $template->id,
                'user_id' => $template->user_id,
            ]);
        }

        return ResponseTemplateData::from([
            ...$this->validated(),
            'user_id' => $this->user()->id,
        ]);
    }
}
