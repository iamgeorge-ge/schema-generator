<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Filament Path
    |--------------------------------------------------------------------------
    |
    | The default path where the Filament panel will be accessible from.
    | This is used by the package to ensure the correct routes are registered.
    |
    */
    'path' => 'schema',

    /*
    |--------------------------------------------------------------------------
    | Generation Paths
    |--------------------------------------------------------------------------
    |
    | Configure where generated files like models and migrations should be stored.
    |
    */
    'paths' => [
        'models' => app_path('Models'),
        'migrations' => database_path('migrations'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Generation Options
    |--------------------------------------------------------------------------
    |
    | Configure default options for code generation.
    |
    */
    'generation' => [
        'timestamps' => true,
        'soft_deletes' => false,
        'fillable' => true,
        'namespace' => 'App\\Models',
    ],

    /*
    |--------------------------------------------------------------------------
    | Schema Default Settings
    |--------------------------------------------------------------------------
    |
    | These options control the default settings for schema generation.
    |
    */

    // Path where models will be created
    'model_path' => app_path('Models'),

    // Default model namespace
    'model_namespace' => 'App\\Models',

    // Default collection name pattern - %s will be replaced with model name
    'collection_name_pattern' => '%sCollection',

    // Whether models should have timestamps by default
    'timestamps' => true,

    // Whether models should have fillable by default
    'fillable' => true,

    // Whether models should have guarded by default
    'guarded' => false,

    // Whether models should use soft deletes by default
    'soft_deletes' => false,

    // API configuration
    'api' => [
        // Whether to include API documentation by default
        'documentation' => true,

        // API version prefix for routes
        'version_prefix' => 'v1',

        // Default API middleware
        'middleware' => ['api'],
    ],

    // Controller configuration
    'controllers' => [
        // Controller namespace
        'namespace' => 'App\\Http\\Controllers',

        // Whether to generate resource controllers by default
        'resource' => true,
    ],

    // Factory configuration
    'factories' => [
        // Whether to generate factories by default
        'enabled' => false,
    ],

    // Field types and their PHP type equivalents for type-hinting
    'field_types' => [
        'string' => 'string',
        'char' => 'string',
        'text' => 'string',
        'mediumText' => 'string',
        'longText' => 'string',
        'integer' => 'integer',
        'tinyInteger' => 'integer',
        'smallInteger' => 'integer',
        'mediumInteger' => 'integer',
        'bigInteger' => 'integer',
        'unsignedBigInteger' => 'integer',
        'unsignedInteger' => 'integer',
        'unsignedSmallInteger' => 'integer',
        'unsignedTinyInteger' => 'integer',
        'float' => 'float',
        'double' => 'float',
        'decimal' => 'float',
        'boolean' => 'boolean',
        'date' => '\\Illuminate\\Support\\Carbon',
        'dateTime' => '\\Illuminate\\Support\\Carbon',
        'dateTimeTz' => '\\Illuminate\\Support\\Carbon',
        'time' => '\\Illuminate\\Support\\Carbon',
        'timeTz' => '\\Illuminate\\Support\\Carbon',
        'timestamp' => '\\Illuminate\\Support\\Carbon',
        'timestampTz' => '\\Illuminate\\Support\\Carbon',
        'year' => 'integer',
        'json' => 'array',
        'jsonb' => 'array',
        'uuid' => 'string',
        'ulid' => 'string',
        'ipAddress' => 'string',
        'macAddress' => 'string',
    ],
];
