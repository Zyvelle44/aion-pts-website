<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        return cache()->remember('website-setting:'.$key, 300, fn () => static::query()->where('key', $key)->value('value') ?? $default);
    }

    public static function putMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            static::query()->updateOrCreate(['key' => $key], ['value' => is_bool($value) ? ($value ? '1' : '0') : $value]);
            cache()->forget('website-setting:'.$key);
        }
    }
}
