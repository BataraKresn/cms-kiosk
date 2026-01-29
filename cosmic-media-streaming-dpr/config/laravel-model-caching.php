<?php

return [
    'cache-prefix' => 'genealabs:laravel-model-caching:',
    'enabled' => env('MODEL_CACHE_ENABLED', true),
    'use-database-keying' => true,
    'cache-driver' => env('MODEL_CACHE_STORE', 'redis'),
];
