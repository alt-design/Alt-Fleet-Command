<?php

namespace AltDesign\FleetCommand\Auth;

use AltDesign\FleetCommand\Models\OAuthToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OAuthController
{
    public function oauthRedirect(
        Request $request
    ) {

        session()->put('oauth.state', $state = Str::random(40));

        return redirect()->to(sprintf(
            '%soauth/authorize?client_id=%s&redirect_uri=%s&scope=&response_type=code&state=%s',
            Str::finish(
                config('services.laravelpassport.host'),
                '/'),
            config('services.laravelpassport.client_id'),
            config('services.laravelpassport.redirect'),
            $state
        ));
    }

    public function laravelPassportCallback(
        Request $request
    ) {
        $request->validate([
            'code' => 'required',
        ]);

        $tokenResponse = Http::asForm()->post(
            config('services.laravelpassport.host') . 'oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.laravelpassport.client_id'),
            'client_secret' => config('services.laravelpassport.client_secret'),
            'redirect_uri' => config('services.laravelpassport.redirect'),
            'code' => $request->get('code'),
        ]);

        try {
            Validator::make($tokenResData = $tokenResponse->json(),
                [
                    'token_type' => 'required',
                    'access_token' => 'required',
                    'refresh_token' => 'required',
                    'expires_in' => 'required',
                ])->validate();
        } catch (\Exception $exception) {
            return redirect()->to(route('login'))->withErrors(
                'OAuth Error',
                'Something went wrong getting OAuth Token'
            );
        }

        if ($user = OAuthToken::make(
            $tokenResponse->json()
        )->updateUserFromOAuth()) {
            Auth::login($user);
        }
        return redirect()->to('/');
    }
}