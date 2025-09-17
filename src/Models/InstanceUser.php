<?php

declare(strict_types=1);

namespace AltDesign\FleetCommand\Models;

use AltDesign\FleetCommand\DTO\UserDTO;
use AltDesign\FleetCommand\Models\Passport\OAuthClient;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InstanceUser extends Model
{
    use HasUuids;

    protected $table = 'instance_users';
    protected $guarded = [];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static function grant(
        UserDTO $user,
        Instance $instance,
    ): self
    {
        return self::firstOrCreate([
            'instance_id' => $instance->id,
            'user_id' => $user->toArray()['id']
        ]);
    }

    public static function revoke(
        UserDTO $user,
        Instance $instance,
    ): void
    {
        self::where('user_id', $user->toArray()['id'])
            ->where('instance_id', $instance->id)
            ->delete();
    }

    public function user(): HasOne
    {
        return $this->hasOne(config('alt-fleet-cmd.central.user_model'), 'id', 'user_id');
    }

    public function instance(): HasOne
    {
        return $this->hasOne(Instance::class, 'id', 'instance_id');
    }
}
