<?php

declare(strict_types=1);

namespace AltDesign\FleetCommand\Models\Passport;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class OAuthClient extends Model
{
    use HasUuids;

    protected $table = 'oauth_clients';

    protected $fillable = [];
}
