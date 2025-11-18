<?php

use Illuminate\Support\Facades\Route;

Route::prefix('oauth')
    ->middleware('web')
    ->group(function () {
        Route::get(
            '/redirect',
            [AltDesign\FleetCommand\Http\Controllers\OAuthController::class, 'redirect']
        )->name('alt-fleet-cmd.oauth.redirect');

        Route::get(
            '/callback',
            [AltDesign\FleetCommand\Http\Controllers\OAuthController::class, 'callback']
        )->name('alt-fleet-cmd.oauth.callback');
    });

Route::prefix('alt-fleet-cmd/users')
    ->middleware('validate.central')
    ->group(function () {
        Route::post(
            '/create',
            [AltDesign\FleetCommand\Http\Controllers\Instance\UserController::class, 'create']
        )->name('alt-fleet-cmd.users.create');

        Route::delete(
            '/delete',
            [AltDesign\FleetCommand\Http\Controllers\Instance\UserController::class, 'delete']
        )->name('alt-fleet-cmd.users.create');
    });
