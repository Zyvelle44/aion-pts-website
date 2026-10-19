<?php

namespace App\Filament\Pages;

use App\Models\InstalledPlugin;
use App\Services\PluginInstaller as PluginInstallerService;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class PluginInstaller extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-puzzle-piece';
    protected static ?string $navigationLabel = 'Plugin Installer';
    protected static ?string $title = 'Plugin Installer';
    protected static ?string $slug = 'plugin-installer';
    protected static string|\UnitEnum|null $navigationGroup = 'System Management';
    protected static ?int $navigationSort = 21;
    protected string $view = 'filament.pages.plugin-installer';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('package')
                ->label('Plugin ZIP')
                ->disk('local')
                ->directory('plugin-uploads')
                ->acceptedFileTypes(['application/zip', 'application/x-zip-compressed'])
                ->maxSize(20480)
                ->required()
                ->helperText('ZIP file · Maximum 20 MB · Install packages only from trusted sources.'),
        ])->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        // Keep package actions inside the page content so the interface stays clear.
        return [];
    }

    public function togglePlugin(string $pluginId): void
    {
        $plugin = InstalledPlugin::query()->where('plugin_id', $pluginId)->firstOrFail();
        try {
            app(PluginInstallerService::class)->setEnabled($pluginId, ! $plugin->is_enabled);
            Notification::make()
                ->title($plugin->is_enabled ? 'Plugin disabled' : 'Plugin enabled')
                ->body($plugin->name.' status was updated.')
                ->success()
                ->send();
        } catch (\Throwable $exception) {
            report($exception);
            Notification::make()->title('Could not update plugin')->body($exception->getMessage())->danger()->persistent()->send();
        }
    }

    public function rollbackPlugin(string $pluginId): void
    {
        try {
            $plugin = app(PluginInstallerService::class)->rollback($pluginId);
            Notification::make()->title('Plugin rolled back')->body($plugin->name.' was restored to v'.$plugin->version.' and remains disabled.')->success()->send();
        } catch (\Throwable $exception) {
            report($exception);
            Notification::make()->title('Rollback failed')->body($exception instanceof \Illuminate\Validation\ValidationException ? collect($exception->errors())->flatten()->implode(' ') : $exception->getMessage())->danger()->persistent()->send();
        }
    }

    public function uninstallPlugin(string $pluginId): void
    {
        try {
            $plugin = InstalledPlugin::query()->where('plugin_id', $pluginId)->firstOrFail();
            $name = $plugin->name;
            app(PluginInstallerService::class)->uninstall($pluginId);
            Notification::make()->title('Plugin uninstalled')->body($name.' was removed.')->success()->send();
        } catch (\Throwable $exception) {
            report($exception);
            Notification::make()->title('Could not uninstall plugin')->body($exception->getMessage())->danger()->persistent()->send();
        }
    }

    public function installPackage(): void
    {
        $state = $this->form->getState();
        $path = $state['package'] ?? null;
        if (! is_string($path) || $path === '') {
            Notification::make()->title('Choose a plugin ZIP first')->danger()->send();
            return;
        }

        try {
            $plugin = app(PluginInstallerService::class)->install($path);
            Storage::disk('local')->delete($path);
            $this->form->fill(['package' => null]);
            Notification::make()->title('Plugin installed or updated')->body($plugin->name.' v'.$plugin->version.' is installed and disabled. Review it, then enable it when ready. Previous versions are backed up when updating.')->success()->send();
        } catch (\Throwable $exception) {
            report($exception);
            Notification::make()->title('Plugin installation failed')->body($exception instanceof \Illuminate\Validation\ValidationException ? collect($exception->errors())->flatten()->implode(' ') : $exception->getMessage())->danger()->persistent()->send();
        }
    }

    protected function getViewData(): array
    {
        $installer = app(PluginInstallerService::class);
        return ['installedPlugins' => InstalledPlugin::query()->orderBy('name')->get(), 'rollbackAvailable' => InstalledPlugin::query()->get()->mapWithKeys(fn (InstalledPlugin $plugin): array => [$plugin->plugin_id => $installer->hasRollback($plugin->plugin_id)])->all()];
    }
}
