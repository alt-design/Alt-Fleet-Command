<?php

namespace AltDesign\FleetCommand\Tests;

use AltDesign\FleetCommand\FleetCommandServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            FleetCommandServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        // Provide a default app key to satisfy encryption/hashing requirements if needed
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));

        // Ensure our package config exists with sensible defaults for tests
        $app['config']->set('alt-fleet-cmd', [
            'instance' => [
                'api_key' => null,
            ],
        ]);
    }
}
