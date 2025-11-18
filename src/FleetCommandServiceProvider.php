<?php

declare(strict_types=1);

namespace AltDesign\FleetCommand;

use AltDesign\FleetCommand\Console\Commands\ProvisionInstance;
use AltDesign\FleetCommand\Models\Environment as EnvironmentModel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class FleetCommandServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishMigrations()
            ->publishConfiguration()
            ->loadEnvironment()
            ->loadMiddlewares()
            ->loadRoutes()
            ->registerCommands();
    }

    public function publishMigrations(): self
    {
        $this->publishes([
            __DIR__.'/../database/migrations/central/' => database_path('migrations/alt-fleet-cmd'),
        ], 'alt-fleet-cmd-central-migrations');

        $this->publishes([
            __DIR__.'/../database/migrations/instance/' => database_path('migrations/alt-fleet-cmd'),
        ], 'alt-fleet-cmd-instance-migrations');

        return $this;
    }

    public function publishConfiguration(): self
    {
        $this->publishes([
            __DIR__.'/../config/alt-fleet-cmd.php' => config_path('alt-fleet-cmd.php'),
        ], 'alt-fleet-cmd-config');

        return $this;
    }

    public function loadRoutes(): self
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/common.php');
        $packageConfig = config('alt-fleet-cmd.configuration');
        switch ($packageConfig) {
            case 'instance':
                $this->loadRoutesFrom(__DIR__.'/../routes/instance.php');
                break;
            case 'central':
                $this->loadRoutesFrom(__DIR__.'/../routes/central.php');
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

    public function loadEnvironment(): self
    {
        if (Schema::hasTable('environments')) {
            $map = [
                'FLEET_COMMAND_OAUTH_CLIENT_ID' => 'alt-fleet-cmd.oauth.client_id',
                'FLEET_COMMAND_OAUTH_CLIENT_SECRET' => 'alt-fleet-cmd.oauth.client_secret',
                'FLEET_COMMAND_INSTANCE_API_KEY' => 'alt-fleet-cmd.instance.api_key',
            ];

            foreach ($map as $envKey => $configKey) {
                $val = EnvironmentModel::getValue($envKey);
                if ($val !== null && $val !== '') {
                    Config::set($configKey, $val);
                }
            }
        }

        return $this;
    }

    public function registerCommands(): self
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ProvisionInstance::class,
            ]);
        }

        return $this;
    }
}
