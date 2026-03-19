<?php

use Waad\Metadata\Models\Metadata;

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
    'model' => Metadata::class,

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
        'enabled' => false,
        'ttl' => 3600, // seconds (1 hour)
        'store' => null, // null = default cache driver
        'prefix' => 'model_metadata',
    ],

];
