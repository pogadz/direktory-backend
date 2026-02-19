<?php

// Only loaded when scribe is installed (dev dependency).
// Uses plain string values instead of scribe class constants so this file
// can be parsed safely even when --no-dev composer installs are used.

if (!class_exists(\Knuckles\Scribe\Config\Defaults::class)) {
    return [];
}

use Knuckles\Scribe\Config\Defaults;
use Knuckles\Scribe\Extracting\Strategies;

use function Knuckles\Scribe\Config\configureStrategy;

return [
    'title' => config('app.name') . ' API Documentation',

    'description' => 'REST API for the Direktory platform. Authenticate using a Sanctum bearer token.',

    'intro_text' => 'This documentation covers all available API endpoints. Use the Login or Register endpoints to obtain a bearer token, then include it as `Authorization: Bearer {token}` on authenticated requests.',

    'base_url' => config('app.url'),

    'routes' => [
        [
            'match' => [
                'prefixes' => ['api/*'],
                'domains'  => ['*'],
            ],
            'include' => [],
            'exclude' => [],
        ],
    ],

    'type'  => 'laravel',
    'theme' => 'default',

    'static' => [
        'output_path' => 'public/docs',
    ],

    'laravel' => [
        'add_routes'       => true,
        'docs_url'         => '/docs',
        'assets_directory' => null,
        'middleware'       => [],
    ],

    'external' => [
        'html_attributes' => [],
    ],

    'try_it_out' => [
        'enabled'  => true,
        'base_url' => null,
        'use_csrf' => false,
        'csrf_url' => '/sanctum/csrf-cookie',
    ],

    'auth' => [
        'enabled'     => true,
        'default'     => true,
        'in'          => 'bearer',      // replaces AuthIn::BEARER->value
        'name'        => 'Authorization',
        'use_value'   => env('SCRIBE_AUTH_KEY'),
        'placeholder' => '{YOUR_AUTH_KEY}',
        'extra_info'  => 'Obtain a token by calling the <b>Login</b> or <b>Register</b> endpoints, then pass it as a Bearer token in the Authorization header.',
    ],

    'example_languages' => ['bash', 'javascript'],

    'postman' => [
        'enabled'   => true,
        'overrides' => [],
    ],

    'openapi' => [
        'enabled'    => true,
        'version'    => '3.0.3',
        'overrides'  => [],
        'generators' => [],
    ],

    'groups' => [
        'default' => 'Endpoints',
        'order'   => [],
    ],

    'logo' => false,

    'last_updated' => 'Last updated: {date:F j, Y}',

    'examples' => [
        'faker_seed'    => 1234,
        'models_source' => ['factoryCreate', 'factoryMake', 'databaseFirst'],
    ],

    'strategies' => [
        'metadata' => [
            ...Defaults::METADATA_STRATEGIES,
        ],
        'headers' => [
            ...Defaults::HEADERS_STRATEGIES,
            Strategies\StaticData::withSettings(data: [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ]),
        ],
        'urlParameters' => [
            ...Defaults::URL_PARAMETERS_STRATEGIES,
        ],
        'queryParameters' => [
            ...Defaults::QUERY_PARAMETERS_STRATEGIES,
        ],
        'bodyParameters' => [
            ...Defaults::BODY_PARAMETERS_STRATEGIES,
        ],
        'responses' => configureStrategy(
            Defaults::RESPONSES_STRATEGIES,
            Strategies\Responses\ResponseCalls::withSettings(only: [])
        ),
        'responseFields' => [
            ...Defaults::RESPONSE_FIELDS_STRATEGIES,
        ],
    ],

    'database_connections_to_transact' => [],

    'fractal' => [
        'serializer' => null,
    ],
];
