<?php

declare(strict_types=1);

namespace AltDesign\FleetCommand\Http\Controllers\Central;

use AltDesign\FleetCommand\Models\Instance;
use AltDesign\FleetCommand\Models\Passport\OAuthClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProvisioningController
{
    public function provision(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'base_url' => 'required|url',
            'secret' => 'required|string',
        ]);

        // Validate shared provisioning secret
        $sharedSecret = config('alt-fleet-cmd.provisioning.secret');
        if (! $sharedSecret || ! hash_equals($sharedSecret, (string) $request->string('secret'))) {
            return response()->json([
                'message' => 'Unauthorized: invalid provisioning secret',
            ], 401);
        }

        $baseUrl = Str::finish((string) $request->string('base_url'), '/');
        $name = (string) $request->string('name');

        // If instance already exists, return its credentials instead of creating duplicates
        $existing = Instance::where('base_url', $baseUrl)->first();
        if ($existing) {
            $client = OAuthClient::where('id', $existing->client_id)->first();

            return response()->json([
                'client_id' => $existing->client_id,
                'client_secret' => $client?->secret,
                'api_key' => $existing->api_key,
                'message' => 'Instance already provisioned.',
            ]);
        }

        $apiKey = Str::random(64);

        $clientId = Str::uuid()->toString();
        $clientSecret = Str::random(64);

        DB::transaction(function () use ($clientId, $clientSecret, $name, $baseUrl, $apiKey) {
            /** @var OAuthClient $client */
            $client = new OAuthClient;
            $client->id = $clientId;

            $usesNewSchema = Schema::hasColumn('oauth_clients', 'redirect_uris')
                && Schema::hasColumn('oauth_clients', 'grant_types');

            // Common columns across schemas
            $client->name = $name;

            // Provider column is nullable in both schemas (when present)
            if (Schema::hasColumn('oauth_clients', 'provider')) {
                $client->provider = null;
            }

            if ($usesNewSchema) {
                // Newer Passport schema (nullableMorphs owner, redirect_uris/grant_types JSON)
                if (Schema::hasColumn('oauth_clients', 'owner_type')) {
                    $client->owner_type = null;
                }
                if (Schema::hasColumn('oauth_clients', 'owner_id')) {
                    $client->owner_id = null;
                }

                // Store a hashed secret in DB, return plaintext to caller
                $client->secret = Hash::make($clientSecret);

                // Redirect URIs should be a JSON array; use instance callback URL
                $redirectUri = rtrim($baseUrl, '/').'/oauth/callback';
                $client->redirect_uris = json_encode([$redirectUri]);

                // Grant types as JSON array (authorization_code + refresh_token)
                $client->grant_types = json_encode(['authorization_code', 'refresh_token']);

                // Revocation flag
                if (Schema::hasColumn('oauth_clients', 'revoked')) {
                    $client->revoked = false;
                }
            } else {
                // Legacy Passport schema (redirect + personal/password flags)
                // Only set user_id if column exists
                if (Schema::hasColumn('oauth_clients', 'user_id')) {
                    $client->user_id = null;
                }

                // Secret stored in plaintext in older schemas
                $client->secret = $clientSecret;

                if (Schema::hasColumn('oauth_clients', 'redirect')) {
                    $client->redirect = $baseUrl; // not used for password clients
                }
                if (Schema::hasColumn('oauth_clients', 'personal_access_client')) {
                    $client->personal_access_client = false;
                }
                if (Schema::hasColumn('oauth_clients', 'password_client')) {
                    $client->password_client = true;
                }
                if (Schema::hasColumn('oauth_clients', 'revoked')) {
                    $client->revoked = false;
                }
            }

            $client->save();

            // Create central record of the instance
            $instance = new Instance;
            $instance->name = $name;
            $instance->base_url = $baseUrl;
            $instance->client_id = $clientId;
            $instance->api_key = $apiKey;
            $instance->save();
        });

        return response()->json([
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'api_key' => $apiKey,
            'message' => 'Provisioned successfully',
        ]);
    }
}
