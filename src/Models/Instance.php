<?php

declare(strict_types=1);

namespace AltDesign\FleetCommand\Models;

use AltDesign\FleetCommand\Models\Passport\OAuthClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Instance extends Model
{
    use HasUuids;

    protected $table = 'instances';
    protected $guarded = [];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function oauth_client(): HasOne
    {
        return $this->hasOne(OAuthClient::class, 'id', 'client_id');
    }
}
