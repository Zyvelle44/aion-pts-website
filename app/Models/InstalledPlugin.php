<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class InstalledPlugin extends Model
{
    protected $table = 'installed_plugins';

    protected $fillable = [
        'plugin_id', 'name', 'version', 'type', 'description', 'is_enabled', 'manifest', 'installed_path',
    ];

    protected function casts(): array
    {
        return ['is_enabled' => 'boolean', 'manifest' => 'array'];
    }

    protected static function booted(): void
    {
        $clear = static function (): void {
            Cache::forget('aion-installed-plugin-navigation');
            Cache::forget('aion-installed-plugin-hooks');
        };
        static::saved($clear);
        static::deleted($clear);
    }
}
