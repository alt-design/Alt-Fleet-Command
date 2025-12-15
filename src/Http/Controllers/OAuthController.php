<?php

declare(strict_types=1);

namespace AltDesign\FleetCommand\Http\Controllers;

use AltDesign\FleetCommand\Models\OAuthToken;
use AltDesign\FleetCommand\Services\Instance\GetOAuthUserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OAuthController
{
    public function redirect(
        Request $request
    ) {
        if (config('alt-fleet-cmd.debug')) {
            Log::debug('OAuth redirect initiated');
        }
        session()->put('oauth.state', $state = Str::random(40));

        $authorizeUrl = sprintf(
            '%soauth/authorize?client_id=%s&redirect_uri=%s&scope=&response_type=code&state=%s',
            Str::finish(
                config('alt-fleet-cmd.oauth.host'),
                '/'),
            config('alt-fleet-cmd.oauth.client_id'),
            config('alt-fleet-cmd.oauth.redirect'),
            $state
        );

        if (config('alt-fleet-cmd.debug')) {
            Log::debug('Redirecting to OAuth authorize URL', [
                // avoid logging secrets; URL contains only public params
                'url' => $authorizeUrl,
                'state' => $state,
            ]);
        }

        return redirect()->to($authorizeUrl);
    }

    public function callback(
        Request $request
    ) {
        if (config('alt-fleet-cmd.debug')) {
            Log::debug('OAuth callback received', [
                'query' => $request->query(),
            ]);
        }
        $request->validate([
            'code' => 'required',
        ]);

        $tokenResponse = Http::asForm()->post(
            config('alt-fleet-cmd.oauth.host').'oauth/token', [
                'grant_type' => 'authorization_code',
                'client_id' => config('alt-fleet-cmd.oauth.client_id'),
                'client_secret' => config('alt-fleet-cmd.oauth.client_secret'),
                'redirect_uri' => config('alt-fleet-cmd.oauth.redirect'),
                'code' => $request->get('code'),
            ]);

        if (config('alt-fleet-cmd.debug')) {
            Log::debug('Token endpoint response', [
                'status' => $tokenResponse->status(),
                // do not log tokens or secrets; log keys only
                'keys' => array_keys((array) $tokenResponse->json()),
            ]);
        }

        try {
            Validator::make($tokenResData = $tokenResponse->json(),
                [
                    'token_type' => 'required',
                    'access_token' => 'required',
                    'refresh_token' => 'required',
                    'expires_in' => 'required',
                ])->validate();
        } catch (\Exception $exception) {
            if (config('alt-fleet-cmd.debug')) {
                Log::debug('OAuth token validation failed', [
                    'error' => $exception->getMessage(),
                    'status' => $tokenResponse->status(),
                    'body' => $tokenResponse->json(),
                ]);
            }
            return redirect()->to(route('login'))->withErrors(
                'OAuth Error',
                'Something went wrong getting OAuth Token'
            );
        }

        $tokenModel = OAuthToken::make(
            $tokenResponse->json()
        )->toSession();

        $request->session()->save();

        $userResponse = new GetOAuthUserService($tokenModel->access_token)();
        if (! $userResponse->successful()) {
            if (config('alt-fleet-cmd.debug')) {
                Log::debug('Fetching OAuth user failed', [
                    'status' => $userResponse->status(),
                    'body' => $userResponse->json(),
                ]);
            }
            return redirect()->route('login')->withErrors('OAuth Error', 'Something went wrong getting user');
        }

        $userData = $userResponse->json();
        if (config('alt-fleet-cmd.debug')) {
            Log::debug('OAuth user fetched', [
                'user_id' => $userData['id'] ?? null,
            ]);
        }
        $userModelConfig = config('alt-fleet-cmd.instance.user_model');
        if (! $userData['id']) {
            if (config('alt-fleet-cmd.debug')) {
                Log::debug('OAuth user missing id');
            }
            return redirect()->route('login')->withErrors('OAuth Error', 'User ID not found from Central');
        }

        if (! $user = $userModelConfig::find($userData['id'])) {
            if (config('alt-fleet-cmd.debug')) {
                Log::debug('User not found on instance', [
                    'user_id' => $userData['id'],
                ]);
            }
            return redirect()->route('login')->withErrors('OAuth Error', 'User not found on Instance');
        }

        $user->update($userData);
        Auth::login($user);

        if (config('alt-fleet-cmd.debug')) {
            Log::debug('User logged in via OAuth', [
                'user_id' => $user->id,
            ]);
        }

        return redirect()->to('/dashboard');
    }
}
