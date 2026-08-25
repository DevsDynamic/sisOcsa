<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value'];

    protected function casts(): array
    {
        return ['value' => 'encrypted'];
    }

    public static function valueFor(string $key, mixed $fallback = null): mixed
    {
        if (!Schema::hasTable('system_settings')) {
            return $fallback;
        }

        return static::query()->where('key', $key)->first()?->value ?? $fallback;
    }
}
