<?php

declare(strict_types=1);

namespace AltDesign\FleetCommand\Traits\Central\User;

use AltDesign\FleetCommand\DTO\UserDTO;
use AltDesign\FleetCommand\Models\Instance;
use AltDesign\FleetCommand\Services\Central\PushUserToInstanceService;
use AltDesign\FleetCommand\Services\Central\RevokeUserFromInstanceService;

/**
 * Trait SyncsWithInstances
 *
 * Apply this trait to your central application's User model to gain
 * convenience methods for pushing to, and revoking from, a specific Instance.
 *
 * Example:
 *   use AltDesign\FleetCommand\Traits\Central\User\SyncsWithInstances;
 *
 *   class User extends Authenticatable {
 *       use SyncsWithInstances;
 *   }
 */
trait SyncsWithInstances
{
    /**
     * Push the current user to the provided Instance.
     *
     * @return mixed The HTTP response from the instance create endpoint.
     */
    public function pushToInstance(Instance $instance): mixed
    {
        $dto = $this->toFleetUserDTO();

        return new PushUserToInstanceService($dto, $instance)();
    }

    /**
     * Revoke the current user from the provided Instance (and delete on instance).
     *
     * @return mixed The HTTP response from the instance delete endpoint.
     */
    public function revokeFromInstance(Instance $instance): mixed
    {
        $dto = $this->toFleetUserDTO();

        return new RevokeUserFromInstanceService($dto, $instance)();
    }

    /**
     * Alias of revokeFromInstance for semantic symmetry (push/pull).
     */
    public function pullFromInstance(Instance $instance): mixed
    {
        return $this->revokeFromInstance($instance);
    }

    /**
     * Build a Fleet Command UserDTO from the current model instance.
     */
    protected function toFleetUserDTO(): UserDTO
    {
        return UserDTO::make(
            id: $this->id,
            name: $this->name,
            email: $this->email,
        );
    }
}
