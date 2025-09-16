<?php

namespace AltDesign\FleetCommand\Services\Instance;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GetOAuthUserService
{
    public function __construct(
        public string $access_token
    ) {
        //
    }

    public function __invoke(): mixed
    {
        return Http::withToken($this->access_token)
            ->get(
                Str::finish(config('alt-fleet-cmd.instance.central_url'), "/") .
                    "alt-fleet-cmd/oauth/get-user"
            );
    }
}