<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Table Name
    |--------------------------------------------------------------------------
    |
    | The table name for storing metadata.
    |
    */
    'table' => 'model_metadata',

    /*
    |--------------------------------------------------------------------------
    | Model Class
    |--------------------------------------------------------------------------
    |
    | The model class for storing metadata.
    |
    */
    'model' => Waad\Metadata\Models\Metadata::class,

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Configure metadata caching to reduce database queries.
    | When enabled, read operations are cached and automatically
    | invalidated on any write operation (create, update, delete, etc.).
    |
    */
    'cache' => [
        'enabled' => env('MODEL_METADATA_CACHE_ENABLED', false),
        'ttl' => env('MODEL_METADATA_CACHE_TTL', 3600), // seconds default (1 hour)
        'store' => env('MODEL_METADATA_CACHE_STORE', null), // null = default cache driver
        'prefix' => env('MODEL_METADATA_CACHE_PREFIX', 'model_metadata'),
    ],

];
