<?php

return [
    'appid'    => env('ZALO_APP_ID', '554'),
    'key1' => env('ZALO_KEY1', ''),
    'key2'        => env('ZALO_KEY2', ''),
    'return_url' => env('ZALO_RETURN_URL', ''),
    'callback_url' => env('ZALO_CALLBACK_URL', rtrim((string) env('APP_URL', ''), '/') . '/api/zalo_return'),
    'endpoint'  => env('ENDPOINT_ZALO_PAY_SANDBOX', 'https://sb-openapi.zalopay.vn/v2/create'),
];
