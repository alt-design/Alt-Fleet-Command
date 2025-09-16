<?php

namespace AltDesign\FleetCommand\Http\Controllers;

use AltDesign\FleetCommand\Models\OAuthToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OAuthController
{
    public function redirect(
        Request $request
    ) {
        session()->put('oauth.state', $state = Str::random(40));

        return redirect()->to( sprintf(
            '%soauth/authorize?client_id=%s&redirect_uri=%s&scope=&response_type=code&state=%s',
            Str::finish(
                config('alt-fleet-cmd.oauth.host'),
                '/'),
            config('alt-fleet-cmd.oauth.client_id'),
            config('alt-fleet-cmd.oauth.redirect'),
            $state
        ));
    }

    public function callback(
        Request $request
    ) {
        $request->validate([
            'code' => 'required',
        ]);

        $tokenResponse = Http::asForm()->post(
            config('alt-fleet-cmd.oauth.host') . 'oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => config('alt-fleet-cmd.oauth.client_id'),
            'client_secret' => config('alt-fleet-cmd.oauth.client_secret'),
            'redirect_uri' => config('alt-fleet-cmd.oauth.redirect'),
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

        OAuthToken::make(
            $tokenResponse->json()
        )->toSession();

        return redirect()->to('/');
    }
}