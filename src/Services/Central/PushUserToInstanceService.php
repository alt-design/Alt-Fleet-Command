<?php

declare(strict_types=1);

namespace AltDesign\FleetCommand\Services\Central;

use AltDesign\FleetCommand\DTO\UserDTO;
use AltDesign\FleetCommand\Models\Instance;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PushUserToInstanceService
{
    public function __construct(
        public UserDTO $user,
        public Instance $instance,
    ) {
        //
    }
    public function __invoke(): mixed
    {
        return Http::withToken(
            $this->instance->api_key
        )->post(
            Str::finish($this->instance->base_url, "/") . "alt-fleet-cmd/users/create",
            $this->user->toArray()
        );
    }
}