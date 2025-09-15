<?php

declare(strict_types=1);

namespace AltDesign\FleetCommand\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class OAuthToken extends Model
{
    use HasFactory;

    protected $table = 'oauth_tokens';
    protected $guarded = [];
    protected $casts = [
        'expires_at' => 'datetime'
    ];

    public static function make(
        array $data
    ):self {
        return self::create([
            'token_type' => $data['token_type'],
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'expires_at' => now()->addSeconds($data['expires_in']),
        ]);
    }

    public function toSession(): self
    {
        session()->put('oauth.token_id', $tokenModel->id);
        return $this;
    }

    public static function fromSession(): ?self
    {
        return self::find(
            session()->get('oauth.token_id')
        );
    }
}
