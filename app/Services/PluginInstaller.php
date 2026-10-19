<?php

namespace App\Services;

use App\Models\InstalledPlugin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use ZipArchive;

class PluginInstaller
{
    private const MAX_FILES = 500;
    private const MAX_UNCOMPRESSED_BYTES = 25_000_000;

    public function install(string $uploadedPath): InstalledPlugin
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('PHP zip extension is required to install plugin packages.');
        }

        $zipPath = Storage::disk('local')->path($uploadedPath);
        if (! is_file($zipPath)) {
            throw new RuntimeException('Uploaded plugin ZIP could not be found.');
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw ValidationException::withMessages(['package' => 'The uploaded file is not a valid ZIP archive.']);
        }

        $stageRoot = storage_path('app/aion-plugin-staging/'.Str::uuid());
        File::ensureDirectoryExists($stageRoot);
        $manifest = null;
        $totalBytes = 0;
        try {
            if ($zip->numFiles < 1 || $zip->numFiles > self::MAX_FILES) {
                throw ValidationException::withMessages(['package' => 'Plugin package has too many files or is empty.']);
            }

            $seenPaths = [];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $name = str_replace('\\', '/', (string) ($stat['name'] ?? ''));
                $normalizedName = rtrim($name, '/');
                if ($name === '' || str_starts_with($name, '/') || preg_match('/(^|\/)\.\.(\/|$)/', $name) || preg_match('/^[A-Za-z]:/', $name) || str_contains($name, "\0")) {
                    throw ValidationException::withMessages(['package' => 'The ZIP contains an unsafe file path.']);
                }
                // Duplicate paths can cause different files to overwrite each other during extraction.
                $pathKey = strtolower($normalizedName);
                if (isset($seenPaths[$pathKey])) {
                    throw ValidationException::withMessages(['package' => 'The ZIP contains duplicate or case-conflicting paths.']);
                }
                $seenPaths[$pathKey] = true;

                // statIndex() does not reliably expose external_attributes. Query the ZIP entry
                // attributes explicitly so symbolic links cannot bypass the package guard.
                $opsys = 0;
                $attributes = 0;
                if ($zip->getExternalAttributesIndex($i, $opsys, $attributes) && $opsys === ZipArchive::OPSYS_UNIX) {
                    $fileType = ($attributes >> 16) & 0170000;
                    if ($fileType === 0120000) {
                        throw ValidationException::withMessages(['package' => 'Symbolic links are not allowed in plugin packages.']);
                    }
                }

                if (str_ends_with($name, '/')) continue;
                $totalBytes += (int) ($stat['size'] ?? 0);
                if ($totalBytes > self::MAX_UNCOMPRESSED_BYTES) {
                    throw ValidationException::withMessages(['package' => 'Plugin package exceeds the 25 MB extracted-size limit.']);
                }
                // Keep the first release deliberately narrow: no Composer files, arbitrary app/ writes,
                // migrations, symlinks, executable public files, or package-level bootstrap scripts.
                $allowed = $name === 'plugin.json'
                    || preg_match('#^resources/views/[A-Za-z0-9_./-]+\.blade\.php$#', $name)
                    || $name === 'routes/web.php'
                    || preg_match('#^src/[A-Za-z0-9_./-]+\.php$#', $name)
                    || preg_match('#^database/migrations/[0-9]{4}_[0-9]{2}_[0-9]{2}_[0-9]{6}_[a-z0-9_]+\.php$#', $name);
                if (! $allowed) {
                    throw ValidationException::withMessages(['package' => 'Unsupported package path: '.$name.'. Allowed: plugin.json, Blade views, routes/web.php, src PHP classes, and timestamped database migrations. Install code packages only from trusted sources.']);
                }
            }

            $manifestContent = $zip->getFromName('plugin.json');
            $manifest = is_string($manifestContent) ? json_decode($manifestContent, true) : null;
            if (! is_array($manifest) || json_last_error() !== JSON_ERROR_NONE) {
                throw ValidationException::withMessages(['package' => 'Package must contain a valid plugin.json at its root.']);
            }
            $this->validateManifest($manifest);
            $pluginId = $manifest['id'];
            $existing = InstalledPlugin::query()->where('plugin_id', $pluginId)->first();

            if (! $zip->extractTo($stageRoot)) {
                throw new RuntimeException('Could not extract plugin package.');
            }
            $target = storage_path('app/aion-plugins/'.$pluginId);
            File::ensureDirectoryExists(dirname($target));

            if ($existing) {
                return $this->updateFromStage($stageRoot, $target, $existing, $manifest);
            }

            if (File::exists($target)) {
                throw new RuntimeException('A plugin directory already exists for this ID but no registry record exists. Inspect it manually before proceeding.');
            }
            File::moveDirectory($stageRoot, $target);

            try {
                $installed = DB::transaction(fn () => InstalledPlugin::query()->create([
                    'plugin_id' => $pluginId,
                    'name' => $manifest['name'],
                    'version' => $manifest['version'],
                    'type' => $manifest['type'],
                    'description' => $manifest['description'] ?? null,
                    'is_enabled' => false,
                    'manifest' => $manifest,
                    'installed_path' => $target,
                ]));
                $this->runPluginMigrations($pluginId);
                return $installed->refresh();
            } catch (\Throwable $e) {
                // Leave a disabled, registered package available for diagnosis if a migration fails.
                if (isset($installed) && $installed instanceof InstalledPlugin) {
                    $installed->is_enabled = false;
                    $installed->save();
                } else {
                    File::deleteDirectory($target);
                }
                throw $e;
            }
        } finally {
            $zip->close();
            if (File::exists($stageRoot)) File::deleteDirectory($stageRoot);
        }
    }

    private function updateFromStage(string $stageRoot, string $target, InstalledPlugin $plugin, array $manifest): InstalledPlugin
    {
        if ($plugin->is_enabled) {
            throw ValidationException::withMessages(['package' => 'Disable this plugin before updating it.']);
        }
        if (version_compare((string) $manifest['version'], (string) $plugin->version, '<=')) {
            throw ValidationException::withMessages(['package' => 'Update version must be newer than the installed version. Use Rollback to restore an earlier release.']);
        }
        $this->assertInstalledFiles($plugin);
        $backupRoot = storage_path('app/aion-plugin-backups/'.$plugin->plugin_id.'/'.now()->format('Ymd_His_u'));
        $backupFiles = $backupRoot.'/package';
        File::ensureDirectoryExists($backupRoot);
        $old = [
            'plugin_id' => $plugin->plugin_id,
            'name' => $plugin->name,
            'version' => $plugin->version,
            'type' => $plugin->type,
            'description' => $plugin->description,
            'manifest' => $plugin->manifest,
            'installed_path' => $plugin->installed_path,
            'is_enabled' => false,
        ];
        if (! File::moveDirectory($target, $backupFiles)) {
            File::deleteDirectory($backupRoot);
            throw new RuntimeException('Could not create a backup of the installed plugin. Update was cancelled.');
        }
        File::put($backupRoot.'/metadata.json', json_encode($old, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        try {
            if (! File::moveDirectory($stageRoot, $target)) {
                throw new RuntimeException('Could not move the updated package into place.');
            }
            $plugin->fill([
                'name' => $manifest['name'],
                'version' => $manifest['version'],
                'type' => $manifest['type'],
                'description' => $manifest['description'] ?? null,
                'manifest' => $manifest,
                'installed_path' => $target,
                'is_enabled' => false,
            ])->save();
            $this->runPluginMigrations($plugin->plugin_id);
            return $plugin->refresh();
        } catch (\Throwable $exception) {
            if (File::isDirectory($target)) File::deleteDirectory($target);
            if (File::isDirectory($backupFiles)) File::moveDirectory($backupFiles, $target);
            // Restore registry metadata together with the old package files.
            $plugin->fill($old)->save();
            File::deleteDirectory($backupRoot);
            throw $exception;
        }
    }

    public function rollback(string $pluginId): InstalledPlugin
    {
        $plugin = InstalledPlugin::query()->where('plugin_id', $pluginId)->firstOrFail();
        if ($plugin->is_enabled) {
            throw ValidationException::withMessages(['package' => 'Disable this plugin before rolling it back.']);
        }
        $backupBase = storage_path('app/aion-plugin-backups/'.$pluginId);
        // A plugin that has never been updated has no backup directory yet.
        // Symfony Finder throws DirectoryNotFoundException for a missing path,
        // so treat a missing backup root as 'no rollback available'.
        if (! File::isDirectory($backupBase)) {
            throw ValidationException::withMessages(['package' => 'No rollback snapshot is available for this plugin.']);
        }

        $snapshots = collect(File::directories($backupBase))
            ->filter(fn (string $dir): bool => is_file($dir.'/metadata.json') && is_dir($dir.'/package'))
            ->sortDesc()->values();
        $snapshot = $snapshots->first();
        if (! $snapshot) {
            throw ValidationException::withMessages(['package' => 'No rollback snapshot is available for this plugin.']);
        }
        $metadata = json_decode((string) File::get($snapshot.'/metadata.json'), true);
        if (! is_array($metadata) || ($metadata['plugin_id'] ?? null) !== $pluginId) {
            throw ValidationException::withMessages(['package' => 'Rollback snapshot metadata is invalid.']);
        }
        $target = storage_path('app/aion-plugins/'.$pluginId);
        $currentSnapshot = storage_path('app/aion-plugin-backups/'.$pluginId.'/'.now()->format('Ymd_His_u'));
        File::ensureDirectoryExists($currentSnapshot);
        if (! File::moveDirectory($target, $currentSnapshot.'/package')) {
            File::deleteDirectory($currentSnapshot);
            throw new RuntimeException('Could not preserve the current plugin before rollback.');
        }
        $currentMetadata = [
            'plugin_id' => $plugin->plugin_id, 'name' => $plugin->name, 'version' => $plugin->version,
            'type' => $plugin->type, 'description' => $plugin->description, 'manifest' => $plugin->manifest,
            'installed_path' => $plugin->installed_path, 'is_enabled' => false,
        ];
        File::put($currentSnapshot.'/metadata.json', json_encode($currentMetadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        try {
            if (! File::moveDirectory($snapshot.'/package', $target)) {
                throw new RuntimeException('Could not restore the selected rollback snapshot.');
            }
            $plugin->fill([
                'name' => $metadata['name'], 'version' => $metadata['version'], 'type' => $metadata['type'],
                'description' => $metadata['description'] ?? null, 'manifest' => $metadata['manifest'] ?? [],
                'installed_path' => $target, 'is_enabled' => false,
            ])->save();
            File::deleteDirectory($snapshot);
            return $plugin->refresh();
        } catch (\Throwable $exception) {
            if (File::isDirectory($target)) File::deleteDirectory($target);
            if (File::isDirectory($currentSnapshot.'/package')) File::moveDirectory($currentSnapshot.'/package', $target);
            throw $exception;
        }
    }

    public function hasRollback(string $pluginId): bool
    {
        $base = storage_path('app/aion-plugin-backups/'.$pluginId);
        // Missing backup roots are normal for plugins that have never been updated.
        if (! File::isDirectory($base)) {
            return false;
        }

        foreach (File::directories($base) as $dir) {
            if (is_file($dir.'/metadata.json') && is_dir($dir.'/package')) return true;
        }
        return false;
    }

    public function setEnabled(string $pluginId, bool $enabled): InstalledPlugin
    {
        $plugin = InstalledPlugin::query()->where('plugin_id', $pluginId)->firstOrFail();
        if ($enabled) {
            $this->assertInstalledFiles($plugin);
        }
        $plugin->is_enabled = $enabled;
        $plugin->save();
        return $plugin;
    }

    public function uninstall(string $pluginId): void
    {
        $plugin = InstalledPlugin::query()->where('plugin_id', $pluginId)->firstOrFail();
        $path = $plugin->installed_path;
        // Remove the registry entry first; route middleware then denies access even if file cleanup fails.
        $plugin->delete();
        if (is_string($path) && str_starts_with($path, storage_path('app/aion-plugins/')) && File::isDirectory($path)) {
            File::deleteDirectory($path);
        }
    }

    private function runPluginMigrations(string $pluginId): void
    {
        $path = storage_path('app/aion-plugins/'.$pluginId.'/database/migrations');
        if (! File::isDirectory($path) || File::files($path) === []) return;

        $exitCode = \Illuminate\Support\Facades\Artisan::call('migrate', [
            '--path' => $path,
            '--realpath' => true,
            '--force' => true,
            '--no-interaction' => true,
        ]);
        if ($exitCode !== 0) {
            $output = trim((string) \Illuminate\Support\Facades\Artisan::output());
            throw new RuntimeException('Plugin migrations failed; the plugin remains disabled. '.$output);
        }
    }

    private function validateManifest(array $manifest): void
    {
        $id = $manifest['id'] ?? '';
        if (! is_string($id) || ! preg_match('/^[a-z][a-z0-9-]{1,59}$/', $id)) {
            throw ValidationException::withMessages(['package' => 'Manifest id must be lowercase kebab-case (2–60 characters).']);
        }
        if (! is_string($manifest['name'] ?? null) || trim($manifest['name']) === '' || mb_strlen($manifest['name']) > 120) {
            throw ValidationException::withMessages(['package' => 'Manifest name is required and must be 120 characters or fewer.']);
        }
        if (! is_string($manifest['version'] ?? null) || ! preg_match('/^\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?$/', $manifest['version'])) {
            throw ValidationException::withMessages(['package' => 'Manifest version must use semantic version format, e.g. 1.0.0.']);
        }
        if (! in_array($manifest['type'] ?? null, ['ui-plugin', 'module'], true)) {
            throw ValidationException::withMessages(['package' => 'Manifest type must be ui-plugin or module.']);
        }
        if (isset($manifest['hooks']['topbar'])) {
            $hook = $manifest['hooks']['topbar'];
            $expectedNamespace = 'aion-plugin-'.$id.'::';
            if (! is_string($hook) || ! str_starts_with($hook, $expectedNamespace) || ! preg_match('/^aion-plugin-[a-z][a-z0-9-]*::[A-Za-z0-9_.-]+$/', $hook)) {
                throw ValidationException::withMessages(['package' => 'Top Bar hook must reference this package view namespace, e.g. aion-plugin-'.$id.'::topbar.']);
            }
        }
        if (isset($manifest['base_path']) && (! is_string($manifest['base_path']) || ($manifest['base_path'] !== '' && ! preg_match('#^[a-z0-9][a-z0-9/-]*$#', $manifest['base_path'])))) {
            throw ValidationException::withMessages(['package' => 'base_path must be a safe relative URL path.']);
        }
        if (isset($manifest['navigation'])) {
            if (! is_array($manifest['navigation']) || count($manifest['navigation']) > 20) {
                throw ValidationException::withMessages(['package' => 'Navigation must be an array with at most 20 items.']);
            }
            foreach ($manifest['navigation'] as $item) {
                if (! is_array($item) || ! is_string($item['label'] ?? null) || trim($item['label']) === '' || mb_strlen($item['label']) > 50) {
                    throw ValidationException::withMessages(['package' => 'Each navigation item needs a label of 1–50 characters.']);
                }
                $url = $item['url'] ?? null;
                if (isset($url) && (! is_string($url) || ! str_starts_with($url, '/'))) {
                    throw ValidationException::withMessages(['package' => 'Navigation URLs must be local paths beginning with /.']);
                }
                $placement = $item['placement'] ?? 'global';
                if (! is_string($placement) || ! in_array($placement, ['global', 'account_sidebar'], true)) {
                    throw ValidationException::withMessages(['package' => 'Navigation placement must be global or account_sidebar.']);
                }
                if (($item['requires_login'] ?? false) !== false && ($item['requires_login'] ?? false) !== true) {
                    throw ValidationException::withMessages(['package' => 'Navigation requires_login must be a boolean.']);
                }
            }
        }
        if (isset($manifest['namespace'])) {
            if (! is_string($manifest['namespace']) || ! preg_match('/^AionPlugin\\\\[A-Z][A-Za-z0-9]*(?:\\\\[A-Z][A-Za-z0-9]*)*$/', $manifest['namespace'])) {
                throw ValidationException::withMessages(['package' => 'Invalid plugin PHP namespace.']);
            }
        }
        if (isset($manifest['filament_resources'])) {
            if (! is_array($manifest['filament_resources']) || count($manifest['filament_resources']) > 20) {
                throw ValidationException::withMessages(['package' => 'filament_resources must be an array with at most 20 entries.']);
            }
            foreach ($manifest['filament_resources'] as $class) {
                if (! is_string($class) || ! preg_match('/^AionPlugin\\\\[A-Z][A-Za-z0-9]*(?:\\\\[A-Z][A-Za-z0-9]*)*$/', $class)
                    || ! str_starts_with($class, (string) ($manifest['namespace'] ?? ''))) {
                    throw ValidationException::withMessages(['package' => 'Filament resource classes must use the declared plugin namespace.']);
                }
            }
        }
        if (isset($manifest['routes']) && $manifest['routes'] !== 'routes/web.php') {
            throw ValidationException::withMessages(['package' => 'Only routes/web.php is supported as a plugin route file.']);
        }
    }

    private function assertInstalledFiles(InstalledPlugin $plugin): void
    {
        $base = storage_path('app/aion-plugins/'.$plugin->plugin_id);
        $manifestPath = $base.'/plugin.json';

        if (! is_dir($base) || ! is_file($manifestPath)) {
            throw ValidationException::withMessages(['package' => 'Installed plugin files are missing. Reinstall the package before enabling it.']);
        }

        $manifest = is_array($plugin->manifest) ? $plugin->manifest : [];

        // Validate every declared integration before enabling the package. This prevents a
        // successful Enable action from breaking the public layout with a missing Blade view.
        $topbar = $manifest['hooks']['topbar'] ?? null;
        if (filled($topbar)) {
            $expectedNamespace = 'aion-plugin-'.$plugin->plugin_id.'::';
            if (! is_string($topbar) || ! str_starts_with($topbar, $expectedNamespace)) {
                throw ValidationException::withMessages(['package' => 'The Top Bar hook must use this plugin namespace: '.$expectedNamespace.'view-name']);
            }

            $viewName = substr($topbar, strlen($expectedNamespace));
            if ($viewName === '' || ! preg_match('/^[A-Za-z0-9_.-]+$/', $viewName)) {
                throw ValidationException::withMessages(['package' => 'The Top Bar hook contains an invalid view name.']);
            }

            $viewPath = $base.'/resources/views/'.str_replace('.', '/', $viewName).'.blade.php';
            if (! is_file($viewPath)) {
                throw ValidationException::withMessages(['package' => 'The Top Bar view is missing: resources/views/'.str_replace('.', '/', $viewName).'.blade.php']);
            }
        }

        if (($manifest['routes'] ?? null) === 'routes/web.php' && ! is_file($base.'/routes/web.php')) {
            throw ValidationException::withMessages(['package' => 'The manifest declares routes/web.php, but that file is missing.']);
        }

        foreach (($manifest['navigation'] ?? []) as $item) {
            $url = $item['url'] ?? null;
            if (is_string($url) && str_starts_with($url, '/') && str_contains($url, '//')) {
                throw ValidationException::withMessages(['package' => 'Plugin navigation URLs must be local paths and cannot contain protocol-relative URLs.']);
            }
        }
    }
}
