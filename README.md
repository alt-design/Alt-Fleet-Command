Alt Design Fleet Command

An opinionated Laravel package to coordinate a “central” application with multiple “instance” applications. It provides:

- Publishable migrations for central and instance databases
- Configuration for central/instance roles
- OAuth setup between central and instance
- A provisioning flow to securely register an instance with central
- Middleware to validate calls coming from central to an instance


Setup (step-by-step)

Use this concrete checklist to get up and running quickly. Adjust URLs and names for your environment.

1) Pull your central and instance applications

- Make sure you have both Laravel apps locally (or on your target environments): one will be “central”, the other an “instance”.

2) Install the Fleet Command addon/package in both apps

- composer require alt-design/fleet-command

3) Configure environment variables

- Both apps
  - FLEET_CMD_PROVISIONING_SECRET=your-shared-secret

- Central app
  - FLEET_COMMAND_CONFIGURATION=central

- Instance app
  - FLEET_COMMAND_CONFIGURATION=instance
  - FLEET_COMMAND_CENTRAL_URL=https://aquam-pipe-webapp-central.test/
    - Replace with your real central URL.

Note: You must have Laravel Passport already configured on the central application (migrations installed and keys generated). The package supports common Passport schemas.

4) Central: publish and run migrations, and add user traits

- php artisan vendor:publish --provider="AltDesign\\FleetCommand\\FleetCommandServiceProvider" --tag=alt-fleet-cmd-config
- php artisan vendor:publish --provider="AltDesign\\FleetCommand\\FleetCommandServiceProvider" --tag=alt-fleet-cmd-central-migrations
- Run migrations. Because they publish to database/migrations/alt-fleet-cmd, either:
  - php artisan migrate --path=database/migrations/alt-fleet-cmd
  - or register that folder in your AppServiceProvider to be included by a normal php artisan migrate
- In your central app’s Authenticatable User model, add the traits:
  - use AltDesign\FleetCommand\Traits\Central\User\HasInstances;
  - use AltDesign\FleetCommand\Traits\Central\User\SyncsWithInstances;

5) Instance: publish, migrate, and provision

- php artisan vendor:publish --provider="AltDesign\\FleetCommand\\FleetCommandServiceProvider" --tag=alt-fleet-cmd-config
- php artisan vendor:publish --provider="AltDesign\\FleetCommand\\FleetCommandServiceProvider" --tag=alt-fleet-cmd-instance-migrations
- Run migrations (same path consideration as central):
  - php artisan migrate --path=database/migrations/alt-fleet-cmd
- Provision the instance with central:
  - php artisan alt-fleet-cmd:provision

After provisioning, the instance’s environments table and runtime config will receive the OAuth client_id/client_secret and an API key from central.

6) OAuth/user access note

- OAuth flows should work out of the box. If a user isn’t “pushed” to an instance (i.e., not associated via central), access to that instance should be denied. If you experience unexpected redirects or logouts, verify the user syncing and middleware configuration; you may need to adjust your app’s guards/middleware to match your auth setup.


Quick start

1) Install the package (Composer auto‑discovery is supported)

- composer require alt-design/fleet-command

2) Publish configuration

- php artisan vendor:publish --provider="AltDesign\FleetCommand\FleetCommandServiceProvider" --tag=alt-fleet-cmd-config

This creates config/alt-fleet-cmd.php.

3) Choose a role for each app

- Central app: set FLEET_COMMAND_CONFIGURATION=central
- Instance app: set FLEET_COMMAND_CONFIGURATION=instance


Publishing migrations

Migrations are split per role and are published to database/migrations/alt-fleet-cmd. Because this is a subdirectory, you should run them with the --path option.

- Central migrations
  - php artisan vendor:publish --provider="AltDesign\FleetCommand\FleetCommandServiceProvider" --tag=alt-fleet-cmd-central-migrations
  - php artisan migrate --path=database/migrations/alt-fleet-cmd

- Instance migrations
  - php artisan vendor:publish --provider="AltDesign\FleetCommand\FleetCommandServiceProvider" --tag=alt-fleet-cmd-instance-migrations
  - php artisan migrate --path=database/migrations/alt-fleet-cmd


Configuration reference (config/alt-fleet-cmd.php)

- FLEET_COMMAND_CONFIGURATION
  - Role selector: "central" or "instance" (default: instance).

- provisioning.secret
  - Env: FLEET_CMD_PROVISIONING_SECRET
  - A shared secret used during initial provisioning between central and an instance. Set the same value in both apps.

- oauth.client_id / oauth.client_secret
  - Env: FLEET_COMMAND_OAUTH_CLIENT_ID, FLEET_COMMAND_OAUTH_CLIENT_SECRET
  - Set automatically on the instance after successful provisioning. Can be overridden via env for local/testing.

- oauth.host
  - Env: FLEET_COMMAND_CENTRAL_URL
  - The base URL of the central app; used by the instance during OAuth.

- oauth.redirect
  - Defaults to APP_URL + "/oauth/callback". Used by the instance.

- instance.user_model
  - Default: App\Models\User. Customize if your user model lives elsewhere (instance side).

- instance.api_key
  - Env: FLEET_COMMAND_INSTANCE_API_KEY
  - Set automatically on the instance after successful provisioning. Used by the instance to validate calls from central.

- instance.central_url
  - Env: FLEET_COMMAND_CENTRAL_URL
  - The base URL for your central app (e.g. https://central.example.com).

- central.user_model
  - Default: App\Models\User. Customize for your central app if needed.

- central.passport_auth_guard
  - Default: auth:api. Guard applied to the central OAuth user route.


Routes overview

The service provider always loads routes/common.php (currently empty) and then loads the role‑specific routes based on FLEET_COMMAND_CONFIGURATION.

- Instance routes (routes/instance.php)
  - GET /oauth/redirect → AltDesign\FleetCommand\Http\Controllers\OAuthController@redirect
  - GET /oauth/callback → AltDesign\FleetCommand\Http\Controllers\OAuthController@callback
  - POST /alt-fleet-cmd/users/create → AltDesign\FleetCommand\Http\Controllers\Instance\UserController@create (middleware: validate.central)
  - DELETE /alt-fleet-cmd/users/delete → AltDesign\FleetCommand\Http\Controllers\Instance\UserController@delete (middleware: validate.central)

- Central routes (routes/central.php)
  - GET /alt-fleet-cmd/oauth/get-user → Central user fetch, protected by central.passport_auth_guard
  - POST /alt-fleet-cmd/provision → Provisioning endpoint (throttled), accepts name, base_url, secret


Middleware

- Alias: validate.central → AltDesign\FleetCommand\Http\Middleware\ValidateRequestFromCentral
- Behavior: On instance apps, protects endpoints that should only be callable by central. Expects an Authorization: Bearer <hash> header where <hash> is a bcrypt hash of the instance’s plain API key. The API key is stored in config('alt-fleet-cmd.instance.api_key') and is populated during provisioning.


Provisioning workflow

Provisioning securely registers an instance with central, creates an OAuth client on central, and returns credentials/API key to the instance.

Prerequisites

- On central
  - Set FLEET_COMMAND_CONFIGURATION=central
  - Set FLEET_CMD_PROVISIONING_SECRET=... (a strong random string)
  - Ensure your OAuth/Passport tables exist. The package supports both older and newer Passport schemas.
  - Publish and run central migrations:
    - php artisan vendor:publish --provider="AltDesign\\FleetCommand\\FleetCommandServiceProvider" --tag=alt-fleet-cmd-central-migrations
    - php artisan migrate --path=database/migrations/alt-fleet-cmd

- On instance
  - Set FLEET_COMMAND_CONFIGURATION=instance
  - Set APP_URL=https://instance.example.com and APP_NAME=Your Instance Name
  - Set FLEET_COMMAND_CENTRAL_URL=https://central.example.com
  - Set FLEET_CMD_PROVISIONING_SECRET to the same value as central
  - Publish and run instance migrations:
    - php artisan vendor:publish --provider="AltDesign\\FleetCommand\\FleetCommandServiceProvider" --tag=alt-fleet-cmd-instance-migrations
    - php artisan migrate --path=database/migrations/alt-fleet-cmd

Run provisioning (on the instance)

- php artisan alt-fleet-cmd:provision

What happens

1) The instance posts to https://central.example.com/alt-fleet-cmd/provision with name, base_url, and the shared secret.
2) Central validates the secret and creates:
   - An OAuth client (compatible with both legacy and modern Passport schemas)
   - A central Instances record with the generated client_id and API key
3) Central returns client_id, client_secret, and api_key to the instance.
4) The instance stores those values in its environments table and applies them to runtime config, enabling immediate use.


User/Instance relationship (central)

- The trait AltDesign\FleetCommand\Traits\Central\User\HasInstances provides a belongsToMany() relation to Instances via the instance_users pivot.
- Include the trait on your central app’s Authenticatable user model to access $user->instances.


Environment variables summary

- FLEET_COMMAND_CONFIGURATION=central|instance
- FLEET_CMD_PROVISIONING_SECRET=your-shared-secret (must match between central and instance)
- FLEET_COMMAND_CENTRAL_URL=https://central.example.com (required on instance)
- FLEET_COMMAND_OAUTH_CLIENT_ID, FLEET_COMMAND_OAUTH_CLIENT_SECRET (populated on instance after provisioning)
- FLEET_COMMAND_INSTANCE_API_KEY (populated on instance after provisioning)


Vendor publish tags

- alt-fleet-cmd-config → config/alt-fleet-cmd.php
- alt-fleet-cmd-central-migrations → database/migrations/alt-fleet-cmd (central migrations)
- alt-fleet-cmd-instance-migrations → database/migrations/alt-fleet-cmd (instance migrations)

Examples

Central .env

- FLEET_COMMAND_CONFIGURATION=central
- FLEET_CMD_PROVISIONING_SECRET=base64:YOUR_LONG_RANDOM_STRING

Instance .env

- APP_URL=https://instance.example.com
- APP_NAME=Blue Team
- FLEET_COMMAND_CONFIGURATION=instance
- FLEET_COMMAND_CENTRAL_URL=https://central.example.com
- FLEET_CMD_PROVISIONING_SECRET=base64:YOUR_LONG_RANDOM_STRING


Notes

- If you prefer not to use the environments table on the instance, you can set OAuth and API key values directly via env. The service provider will also attempt to load values from the database on boot if the environments table exists.
- The central provisioning route is aggressively throttled (1 request per 60 minutes per IP) and validates a shared secret for safety.
- Ensure HTTPS is used for all apps and callbacks.
