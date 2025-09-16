<?php

declare(strict_types=1);

namespace AltDesign\FleetCommand\Models\Passport;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class OAuthClient extends Model
{
    use HasUuids;
    protected $table = 'oauth_clients';
    protected $fillable = [];
}
