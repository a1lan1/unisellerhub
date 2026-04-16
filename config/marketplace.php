<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Use Mock API
    |--------------------------------------------------------------------------
    |
    | When set, the application will use local mock endpoints instead of real marketplace APIs.
    |
    */
    'wildberries' => [
        'base_url' => env('WB_BASE_URL', 'https://seller-api.wildberries.ru'),
        'timeout' => 30,
    ],

    'ozon' => [
        'base_url' => env('OZON_BASE_URL', 'https://api-seller.ozon.ru'),
        'timeout' => 30,
    ],

    'moysklad' => [
        'base_url' => env('MOYSKLAD_BASE_URL', 'https://api.moysklad.ru'),
        'timeout' => 30,
    ],

    'yandex' => [
        'base_url' => env('YANDEX_BASE_URL', 'https://api.partner.market.yandex.ru'),
        'timeout' => 30,
    ],

    'avito' => [
        'base_url' => env('AVITO_BASE_URL', 'https://api.avito.ru'),
        'timeout' => 30,
    ],
];
