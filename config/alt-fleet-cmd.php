<?php

use Illuminate\Support\Str;

return [
    'configuration' => env('FLEET_COMMAND_CONFIGURATION', 'instance'),

    'oauth' => [
        'client_id' => env('FLEET_COMMAND_OAUTH_CLIENT_ID', null),
        'client_secret' => env('FLEET_COMMAND_OAUTH_CLIENT_SECRET', null),
        'host' => env('FLEET_COMMAND_CENTRAL_URL'),
        'redirect' => Str::finish(env('APP_URL'), '/') . 'auth/callback',
    ],
];