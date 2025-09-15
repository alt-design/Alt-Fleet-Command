<?php

use Illuminate\Support\Facades\Route;

Route::get('/auth/callback', [AltDesign\FleetCommand\Auth\OAuthController::class, 'callback']);
