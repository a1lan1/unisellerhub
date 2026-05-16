<?php

declare(strict_types=1);

namespace App\Modules\Geo\Interfaces\Http\Requests;

use App\Modules\Geo\Domain\Data\LocationData;
use App\Modules\Geo\Domain\Enums\LocationTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class LocationRequest extends FormRequest
{
    public function rules(): array
    {
        $requiredRule = $this->route('location') ? 'sometimes' : 'required';

        return [
            'name' => [$requiredRule, 'string', 'max:255'],
            'type' => [$requiredRule, new Enum(LocationTypeEnum::class)],
            'address' => [$requiredRule, 'array'],
            'address.full_address' => [$requiredRule, 'string', 'max:255'],
            'address.country' => ['nullable', 'string', 'max:255'],
            'address.city' => ['nullable', 'string', 'max:255'],
            'address.street' => ['nullable', 'string', 'max:255'],
            'address.house_number' => ['nullable', 'string', 'max:255'],
            'address.postal_code' => ['nullable', 'string', 'max:255'],
            'latitude' => [$requiredRule, 'numeric', 'between:-90,90'],
            'longitude' => [$requiredRule, 'numeric', 'between:-180,180'],
            'external_ids' => ['nullable', 'array'],
        ];
    }

    public function toDto(): LocationData
    {
        if ($location = $this->route('location')) {
            return LocationData::from([
                ...$location->toArray(),
                ...$this->validated(),
                'id' => $location->id,
                'user_id' => $location->user_id,
            ]);
        }

        return LocationData::from([
            ...$this->validated(),
            'user_id' => $this->user()->id,
        ]);
    }
}
