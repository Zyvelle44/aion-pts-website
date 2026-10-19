<?php

namespace App\Models;

use App\Filament\Resources\ModulePlugins\ModulePluginResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ModulePlugin extends Model
{
    protected $table = 'module_plugins';

    protected $fillable = [
        'name', 'type', 'slug', 'feature_key', 'version', 'description', 'is_enabled', 'sort_order',
        'dependencies', 'min_app_version', 'max_app_version',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
            'dependencies' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ModulePlugin $record): void {
            // Some deployed databases have a legacy module_key column with a unique index.
            // Populate it before INSERT/UPDATE so SQL Server never receives NULL for this key.
            // The base schema does not require this column, so guard it for compatibility.
            if (Schema::hasColumn($record->getTable(), 'module_key')) {
                $record->module_key = filled($record->slug)
                    ? Str::slug((string) $record->slug)
                    : Str::slug((string) $record->name);
            }

            if ($record->is_enabled && ! ModulePluginResource::isBuiltInFeatureKey($record->feature_key)) {
                throw ValidationException::withMessages([
                    'is_enabled' => 'This entry is registry-only. Integrate its feature key into the application before enabling it.',
                ]);
            }

            if ($record->is_enabled && ! $record->activationReadiness()['ready']) {
                throw ValidationException::withMessages([
                    'is_enabled' => 'This feature cannot be enabled until its dependencies and application-version compatibility are satisfied.',
                ]);
            }
        });

        $clear = static function (ModulePlugin $record): void {
            // Any feature can depend on any other feature, so a change to one
            // registry row invalidates every cached effective feature state.
            foreach (ModulePluginResource::builtInFeatureKeys() as $featureKey) {
                Cache::forget('aion-feature-enabled:'.$featureKey);
            }
            if (filled($record->feature_key)) {
                Cache::forget('aion-feature-enabled:'.$record->feature_key);
            }
            Cache::forget('aion-module-plugin-readiness');
        };

        static::saved($clear);
        static::deleted($clear);
    }

    public function activationReadiness(): array
    {
        $reasons = [];
        $appVersion = (string) config('aion_pts.version', '1.0.0');

        if (! ModulePluginResource::isBuiltInFeatureKey($this->feature_key)) {
            $reasons[] = 'Feature key is not integrated into the application.';
        }

        if (filled($this->min_app_version) && version_compare($appVersion, (string) $this->min_app_version, '<')) {
            $reasons[] = 'Requires AION PTS '.$this->min_app_version.' or newer.';
        }

        if (filled($this->max_app_version) && version_compare($appVersion, (string) $this->max_app_version, '>')) {
            $reasons[] = 'Supports AION PTS up to '.$this->max_app_version.'.';
        }

        $dependencies = is_array($this->dependencies) ? $this->dependencies : [];
        foreach (array_unique(array_filter(array_map(static fn ($value): string => trim((string) $value), $dependencies))) as $dependencyKey) {
            if ($dependencyKey === $this->feature_key) {
                $reasons[] = 'A feature cannot depend on itself.';
                continue;
            }

            $dependency = static::query()->where('feature_key', $dependencyKey)->first();
            if (! $dependency) {
                $reasons[] = 'Missing dependency: '.$dependencyKey.'.';
            } elseif (! $dependency->is_enabled) {
                $reasons[] = 'Dependency is disabled: '.$dependencyKey.'.';
            }
        }

        return [
            'ready' => $reasons === [],
            'reasons' => $reasons,
            'app_version' => $appVersion,
        ];
    }

    public static function featureEnabled(string $key): bool
    {
        return Cache::remember('aion-feature-enabled:'.$key, now()->addSeconds(30), function () use ($key): bool {
            $feature = static::query()->where('feature_key', $key)->where('is_enabled', true)->first();
            return $feature !== null && $feature->activationReadiness()['ready'];
        });
    }
}
