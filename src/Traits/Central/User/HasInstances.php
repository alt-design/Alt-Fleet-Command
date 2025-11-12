<?php

declare(strict_types=1);

namespace AltDesign\FleetCommand\Traits\Central\User;

use AltDesign\FleetCommand\Models\Instance;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Trait HasInstances
 *
 * Pull this trait into your application's authenticatable User model
 * to access the Fleet Command Instances associated with the user via
 * the `instance_users` pivot table.
 *
 * Example:
 *   use AltDesign\FleetCommand\Traits\User\HasInstances;
 *
 *   class User extends Authenticatable {
 *       use HasInstances;
 *   }
 */
trait HasInstances
{
    /**
     * Get the Instances this user belongs to.
     */
    public function instances(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Instance::class,
            table: 'instance_users',
            foreignPivotKey: 'user_id',
            relatedPivotKey: 'instance_id'
        )->withTimestamps();
    }
}
