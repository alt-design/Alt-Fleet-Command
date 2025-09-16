<?php

declare(strict_types=1);

namespace AltDesign\FleetCommand;

use Illuminate\Support\ServiceProvider;

class FleetCommandServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishMigrations()
            ->publishConfiguration()
            ->loadMiddlewares()
            ->loadRoutes();
    }

    public function publishMigrations(): self
    {
        $this->publishes([
            __DIR__ . '/../database/migrations/central/' => database_path('migrations/alt-fleet-cmd'),
        ], 'alt-fleet-cmd-central-migrations');

        $this->publishes([
            __DIR__ . '/../database/migrations/instance/' => database_path('migrations/alt-fleet-cmd'),
        ], 'alt-fleet-cmd-instance-migrations');

        return $this;
    }

    public function publishConfiguration(): self
    {
        $this->publishes([
            __DIR__ . '/../config/alt-fleet-cmd.php' => config_path('alt-fleet-cmd.php'),
        ], 'alt-fleet-cmd-config');

        return $this;
    }

    public function loadRoutes(): self
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/common.php');
        switch (config('alt-fleet-cmd.configuration')) {
            case 'instance':
                $this->loadRoutesFrom(__DIR__ . '/../routes/instance.php');
                break;
            case 'central':
                $this->loadRoutesFrom(__DIR__ . '/../routes/central.php');
                break;
            default:
                throw new \Exception('Invalid Fleet Command Configuration : Please use instance or central');
        }

        return $this;
    }

    public function loadMiddlewares(): self
    {
        $router = $this->app['router'];
        $router->aliasMiddleware(
            'validate.central',
            \AltDesign\FleetCommand\Http\Middleware\ValidateRequestFromCentral::class
        );
        return $this;
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/alt-fleet-cmd.php', 'alt-fleet-cmd'
        );
    }
}