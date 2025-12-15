<?php

use Illuminate\Support\Str;

return [
    'debug' => (bool) env('FLEET_COMMAND_DEBUG', false),
    'configuration' => env('FLEET_COMMAND_CONFIGURATION', 'instance'),

    'provisioning' => [
        // Common secret used between instance and central during initial provisioning
        'secret' => env('FLEET_CMD_PROVISIONING_SECRET'),
    ],

    'oauth' => [
        'client_id' => env('FLEET_COMMAND_OAUTH_CLIENT_ID', null),
        'client_secret' => env('FLEET_COMMAND_OAUTH_CLIENT_SECRET', null),
        'host' => env('FLEET_COMMAND_CENTRAL_URL'),
        'redirect' => Str::finish(env('APP_URL'), '/').'oauth/callback',
    ],

    'instance' => [
        'user_model' => 'App\Models\User',
        'api_key' => env('FLEET_COMMAND_INSTANCE_API_KEY', null),
        'central_url' => env('FLEET_COMMAND_CENTRAL_URL'),
    ],

    'central' => [
        'user_model' => 'App\Models\User',
        'passport_auth_guard' => 'auth:api',
    ],
];
