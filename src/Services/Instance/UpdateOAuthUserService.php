<?php

namespace AltDesign\FleetCommand\Services\Instance;

use AltDesign\FleetCommand\DTO\UserDTO;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class UpdateOAuthUserService
{
    public function __construct(
        public string $access_token,
        public UserDTO $user,
        public array $fields,
    ) {
        //
    }

    public function __invoke(): mixed
    {
        return Http::withToken($this->access_token)
            ->put(
                Str::finish(config('alt-fleet-cmd.instance.central_url'), '/').
                'alt-fleet-cmd/oauth/update-user', [
                    'user' => $this->user->toArray()['id'],
                    'fields' => json_encode($this->fields),
                    'instance_url' => urlencode(config('app.url')),
                ]
            );
    }
}
