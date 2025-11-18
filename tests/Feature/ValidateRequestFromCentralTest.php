<?php

use AltDesign\FleetCommand\Http\Middleware\ValidateRequestFromCentral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {});

function runMiddleware(?string $authorizationHeader, ?callable $closure = null): Response
{
    $server = [];
    if ($authorizationHeader !== null) {
        $server['HTTP_AUTHORIZATION'] = $authorizationHeader;
    }

    $request = Request::create('/', 'GET', server: $server);

    $middleware = new ValidateRequestFromCentral;

    $next = $closure ?? function () {
        return response('OK', 200);
    };

    return $middleware->handle($request, $next);
}

it('returns 401 when bearer token is missing', function () {
    config(['alt-fleet-cmd.instance.api_key' => 'any-secret']);

    $response = runMiddleware(null);

    expect($response->getStatusCode())->toBe(401)
        ->and($response->getContent())->toBe('{"message":"Unauthorized"}');
});

it('returns 401 when api key config is missing', function () {
    config(['alt-fleet-cmd.instance.api_key' => null]);

    // Provide any header; since api_key is missing, should still be 401
    $response = runMiddleware('Bearer '.Hash::make('irrelevant'));

    expect($response->getStatusCode())->toBe(401)
        ->and($response->getContent())->toBe('{"message":"Unauthorized"}');
});

it('returns 401 when bearer hash does not match api key', function () {
    config(['alt-fleet-cmd.instance.api_key' => 'correct-secret']);

    $wrongHash = Hash::make('wrong-secret');

    $response = runMiddleware('Bearer '.$wrongHash);

    expect($response->getStatusCode())->toBe(401)
        ->and($response->getContent())->toBe('{"message":"Unauthorized"}');
});

it('allows the request when bearer hash matches the api key', function () {
    config(['alt-fleet-cmd.instance.api_key' => 'correct-secret']);

    $goodHash = Hash::make('correct-secret');

    $response = runMiddleware('Bearer '.$goodHash);

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getContent())->toBe('OK');
});
