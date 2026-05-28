<?php

declare(strict_types=1);

namespace App\Modules\Geo\Interfaces\Http\Requests;

use App\Modules\Geo\Domain\Data\LocationData;
use App\Modules\Geo\Domain\Enums\LocationTypeEnum;
use App\Modules\Geo\Domain\ValueObjects\Coordinates;
use App\Modules\Shared\Domain\ValueObjects\Address;
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
        $coordinates = new Coordinates(
            latitude: $this->validated('latitude'),
            longitude: $this->validated('longitude')
        );

        $address = new Address(
            fullAddress: $this->validated('address.full_address'),
            country: $this->validated('address.country'),
            city: $this->validated('address.city'),
            street: $this->validated('address.street'),
            houseNumber: $this->validated('address.house_number'),
            postalCode: $this->validated('address.postal_code'),
        );

        if ($location = $this->route('location')) {
            return LocationData::from([
                ...$location->toArray(),
                ...$this->validated(),
                'id' => $location->id,
                'user_id' => $location->user_id,
                'coordinates' => $coordinates,
                'address' => $address,
            ]);
        }

        return LocationData::from([
            ...$this->validated(),
            'user_id' => $this->user()->id,
            'coordinates' => $coordinates,
            'address' => $address,
        ]);
    }
}
