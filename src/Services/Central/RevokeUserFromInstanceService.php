<?php

declare(strict_types=1);

namespace AltDesign\FleetCommand\Services\Central;

use AltDesign\FleetCommand\DTO\UserDTO;
use AltDesign\FleetCommand\Models\Instance;
use AltDesign\FleetCommand\Models\InstanceUser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RevokeUserFromInstanceService
{
    public function __construct(
        public UserDTO $user,
        public Instance $instance,
    ) {
        //
    }
    public function __invoke(): mixed
    {
        InstanceUser::revoke($this->user, $this->instance);

        return Http::withToken(
            $this->instance->api_key
        )->delete(
            Str::finish($this->instance->base_url, "/") . "alt-fleet-cmd/users/delete",
            $this->user->toArray()
        );
    }
}