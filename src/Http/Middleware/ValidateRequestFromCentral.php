<?php

declare(strict_types=1);

namespace AltDesign\FleetCommand\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ValidateRequestFromCentral
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Don't do anything if keys are missing
        $bearerHash = $request->bearerToken();
        if (!$bearerHash) {
            return $this->unauthorisedResponse();
        }

        $apiKey = config('alt-fleet-cmd.instance.api_key');
        if (!$apiKey) {
            return $this->unauthorisedResponse();
        }

        // Check the local plain text API key against the hashed API key with 'Bearer ' prefix
        $hash = Str::after($bearerHash, 'Bearer ');
        if (! Hash::check($apiKey, $hash)) {
            return $this->unauthorisedResponse();
        }

        return $next($request);
    }

    private function unauthorisedResponse(): mixed
    {
        return response(json_encode(['message' => 'Unauthorized']), 401);
    }
}