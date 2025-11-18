<?php

namespace AltDesign\FleetCommand\Console\Commands;

use AltDesign\FleetCommand\Models\Environment as EnvironmentModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProvisionInstance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alt-fleet-cmd:provision';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to provision this instance with central';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Provisioning instance...');
        if (EnvironmentModel::getValue('HAS_PROVISIONED')) {
            $this->warn('Instance has already been provisioned. Exiting...');

            return self::SUCCESS;
        }
        $central = rtrim((string) config('alt-fleet-cmd.instance.central_url'), '/');
        $secret = (string) config('alt-fleet-cmd.provisioning.secret');
        $appUrl = Str::finish((string) env('APP_URL'), '/');
        $name = (string) env('APP_NAME', 'Unnamed Instance');

        if (! $central) {
            $this->error('FLEET_COMMAND_CENTRAL_URL is not configured.');

            return self::FAILURE;
        }
        if (! $secret) {
            $this->error('ALT_FLEET_CMD_PROVISIONING_SECRET is not configured.');

            return self::FAILURE;
        }

        $url = $central.'/alt-fleet-cmd/provision';
        $this->info("Provisioning against: $url");

        $response = Http::asJson()->post($url, [
            'name' => $name,
            'base_url' => $appUrl,
            'secret' => $secret,
        ]);

        if ($response->failed()) {
            $this->error('Provisioning failed: '.$response->status().' '.$response->body());

            return self::FAILURE;
        }

        $data = $response->json();
        $clientId = $data['client_id'] ?? null;
        $clientSecret = $data['client_secret'] ?? null;
        $apiKey = $data['api_key'] ?? null;

        if (! $clientId || ! $clientSecret || ! $apiKey) {
            $this->error('Provisioning response missing expected keys.');

            return self::FAILURE;
        }

        // Persist into environments table
        try {
            EnvironmentModel::setValue('FLEET_COMMAND_OAUTH_CLIENT_ID', (string) $clientId);
            EnvironmentModel::setValue('FLEET_COMMAND_OAUTH_CLIENT_SECRET', (string) $clientSecret);
            EnvironmentModel::setValue('FLEET_COMMAND_INSTANCE_API_KEY', (string) $apiKey);

            // Also set runtime config so the app can immediately use the values
            Config::set('alt-fleet-cmd.oauth.client_id', $clientId);
            Config::set('alt-fleet-cmd.oauth.client_secret', $clientSecret);
            Config::set('alt-fleet-cmd.instance.api_key', $apiKey);

            $this->info('Provisioned successfully. Credentials saved to environments table.');
            EnvironmentModel::setValue('HAS_PROVISIONED', true);
        } catch (\Throwable $e) {
            Log::error('Failed to persist credentials to environments table: '.$e->getMessage());
            $this->warn('Failed to persist credentials to environments table');
        }

        return self::SUCCESS;
    }
}
