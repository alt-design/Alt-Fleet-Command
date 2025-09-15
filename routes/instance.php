<?php

use Illuminate\Support\Facades\Route;

Route::get('/oauth/redirect', [AltDesign\FleetCommand\Auth\OAuthController::class, 'redirect']);
Route::get('/oauth/callback', [AltDesign\FleetCommand\Auth\OAuthController::class, 'callback']);
