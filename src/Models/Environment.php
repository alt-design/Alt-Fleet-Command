<?php

declare(strict_types=1);

namespace AltDesign\FleetCommand\Models;

use Illuminate\Database\Eloquent\Model;

class Environment extends Model
{
    protected $table = 'environments';

    protected $fillable = ['key', 'value'];

    protected $casts = [
        'value' => 'encrypted',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static function getValue(string $key, ?string $default = null): ?string
    {
        $row = static::query()->where('key', $key)->first();

        return $row?->value ?? $default;
    }

    public static function setValue(string $key, string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
