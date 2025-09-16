<?php

use Illuminate\Support\Facades\Route;

Route::prefix('oauth')->group( function () {
    Route::get(
        '/redirect',
        [AltDesign\FleetCommand\Http\Controllers\OAuthController::class, 'redirect']
    )->name('alt-fleet-cmd.oauth.redirect');

    Route::get(
        '/callback',
        [AltDesign\FleetCommand\Http\Controllers\OAuthController::class, 'callback']
    )->name('alt-fleet-cmd.oauth.callback');
});

