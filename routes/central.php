<?php

use AltDesign\FleetCommand\Http\Controllers\Central\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('alt-fleet-cmd/oauth')
    ->middleware(config('alt-fleet-cmd.central.passport_auth_guard'))
    ->group(function () {
    Route::get(
        '/get-user',
        [UserController::class, 'fetch']
    );
});