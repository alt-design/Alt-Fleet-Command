<?php

use AltDesign\FleetCommand\Http\Controllers\Central\ProvisioningController;
use AltDesign\FleetCommand\Http\Controllers\Central\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('alt-fleet-cmd')->group(function () {
    Route::prefix('oauth')
        ->middleware(config('alt-fleet-cmd.central.passport_auth_guard'))
        ->group(function () {
            Route::get(
                '/get-user',
                [UserController::class, 'fetch']
            );
        });

    // Provisioning route protected by shared secret verification inside controller
    Route::post(
        '/provision',
        [ProvisioningController::class, 'provision']
    )->name('alt-fleet-cmd.provision');
});