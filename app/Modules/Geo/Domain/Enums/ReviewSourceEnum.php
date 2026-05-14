<?php

declare(strict_types=1);

namespace App\Modules\Geo\Domain\Enums;

enum ReviewSourceEnum: string
{
    case INTERNAL = 'internal';
    case YELP = 'yelp';
    case GOOGLE = 'google';
    case AIRBNB = 'airbnb';
    case AMAZON = 'amazon';
    case BOOKING = 'booking';
    case TRUSTPILOT = 'trustpilot';
    case TRIPADVISOR = 'tripadvisor';
}
