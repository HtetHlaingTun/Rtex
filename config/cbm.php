<?php
// return [
//     'api_url' => env('CBM_API_URL', 'https://api.cbm.gov.mm/api/v1'),
//     'api_key' => env('CBM_API_KEY'),
//     'timeout' => (int) env('CBM_TIMEOUT', 30),
//     'cache_minutes' => (int) env('CBM_CACHE_MINUTES', 5),
//     'default_factor' => (float) env('CBM_DEFAULT_FACTOR', 1),
// ];
return [
    'api_url' => env('CBM_API_URL', 'https://forex.cbm.gov.mm/api/latest'),
    'api_key' => env('CBM_API_KEY'),
    'timeout' => (int) (env('CBM_TIMEOUT', 30) ?: 30),
    'cache_minutes' => (int) (env('CBM_CACHE_MINUTES', 5) ?: 5),
    'default_factor' => (float) (env('CBM_DEFAULT_FACTOR', 1) ?: 1),
];
