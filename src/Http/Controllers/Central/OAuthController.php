<?php

declare(strict_types=1);

namespace AltDesign\FleetCommand\Http\Controllers\Central;



use AltDesign\FleetCommand\Models\OAuthToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OAuthController
{
    public function getUser(Request $request): mixed
    {
        return response()->json(Auth::user());
    }
}