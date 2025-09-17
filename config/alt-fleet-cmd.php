<?php

use Illuminate\Support\Str;

return [
    'configuration' => env('FLEET_COMMAND_CONFIGURATION', 'instance'),

    'oauth' => [
        'client_id' => env('FLEET_COMMAND_OAUTH_CLIENT_ID', null),
        'client_secret' => env('FLEET_COMMAND_OAUTH_CLIENT_SECRET', null),
        'host' => env('FLEET_COMMAND_CENTRAL_URL'),
        'redirect' => Str::finish(env('APP_URL'), '/') . 'oauth/callback',
    ],

    'instance' => [
        'user_model' => 'App\Models\User',
        'api_key' => env('FLEET_COMMAND_INSTANCE_API_KEY', null),
        'central_url' => env('FLEET_COMMAND_CENTRAL_URL'),
    ],

    'central' => [
        'user_model' => 'App\Models\User',
        'passport_auth_guard' => 'auth:api',
    ]
];