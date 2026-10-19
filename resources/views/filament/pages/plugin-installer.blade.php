<x-filament-panels::page>
    <div style="display: grid; grid-template-columns: minmax(0, 1fr); gap: 24px; align-items: start;">
        {{-- One section only: avoid the previous nested upload card. --}}
        <x-filament::section>
            <x-slot name="heading">Install Plugin Package</x-slot>
            <x-slot name="description">Upload a plugin ZIP. New packages are installed disabled so you can review them before activation.</x-slot>

            <div style="display: grid; gap: 14px;">
                {{ $this->form }}

                <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px; padding-top: 14px; border-top: 1px solid rgb(229 231 235);">
                    <p style="margin: 0; max-width: 680px; font-size: 13px; line-height: 1.65; color: rgb(107 114 128);">
                        <span style="display: block; margin-bottom: 4px; font-weight: 600; color: rgb(55 65 81);">Accepted package contents · Updates require a newer version and create a rollback snapshot</span>
                        <code style="font-size: 12px; white-space: nowrap;">plugin.json</code>, Blade views, and optional <code style="font-size: 12px; white-space: nowrap;">routes/web.php</code>.
                    </p>
                    <x-filament::button
                        type="button"
                        icon="heroicon-o-arrow-up-tray"
                        wire:click="installPackage"
                        wire:loading.attr="disabled"
                        wire:target="installPackage"
                    >
                        <span wire:loading.remove wire:target="installPackage">Install / Update package</span>
                        <span wire:loading wire:target="installPackage">Installing package…</span>
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Installed Packages</x-slot>
            <x-slot name="description">Manage packages independently. Disabling keeps package files installed; uninstalling removes only the selected package files and registry entry.</x-slot>

            @if ($installedPlugins->isEmpty())
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; padding: 36px 16px; text-align: center;">
                    <x-filament::icon icon="heroicon-o-archive-box" style="width: 28px; height: 28px; color: rgb(156 163 175);" />
                    <h3 style="margin: 4px 0 0; font-size: 14px; font-weight: 600;">No packages installed yet</h3>
                    <p style="margin: 0; max-width: 520px; font-size: 13px; line-height: 1.6; color: rgb(107 114 128);">Upload a trusted plugin ZIP above. Installed packages will appear here with their status and available actions.</p>
                </div>
            @else
                <div style="width: 100%; max-width: 100%; overflow-x: auto; border: 1px solid rgb(229 231 235); border-radius: 8px;">
                    <table style="width: 100%; min-width: 1120px; table-layout: fixed; border-collapse: separate; border-spacing: 0; text-align: left;">
                        <colgroup>
                            <col style="width: 230px;">
                            <col style="width: 145px;">
                            <col style="width: 115px;">
                            <col style="width: 125px;">
                            <col style="width: 330px;">
                            <col style="width: 175px;">
                        </colgroup>
                        <thead>
                            <tr style="background: rgba(107, 114, 128, .06);">
                                <th scope="col" style="padding: 13px 16px; border-bottom: 1px solid rgb(229 231 235); font-size: 11px; font-weight: 700; letter-spacing: .055em; text-transform: uppercase; color: rgb(107 114 128);">Plugin</th>
                                <th scope="col" style="padding: 13px 16px; border-bottom: 1px solid rgb(229 231 235); font-size: 11px; font-weight: 700; letter-spacing: .055em; text-transform: uppercase; color: rgb(107 114 128);">Type</th>
                                <th scope="col" style="padding: 13px 16px; border-bottom: 1px solid rgb(229 231 235); font-size: 11px; font-weight: 700; letter-spacing: .055em; text-transform: uppercase; color: rgb(107 114 128);">Version</th>
                                <th scope="col" style="padding: 13px 16px; border-bottom: 1px solid rgb(229 231 235); font-size: 11px; font-weight: 700; letter-spacing: .055em; text-transform: uppercase; color: rgb(107 114 128);">Status</th>
                                <th scope="col" style="padding: 13px 16px; border-bottom: 1px solid rgb(229 231 235); font-size: 11px; font-weight: 700; letter-spacing: .055em; text-transform: uppercase; color: rgb(107 114 128);">Description</th>
                                <th scope="col" style="padding: 13px 16px; border-bottom: 1px solid rgb(229 231 235); font-size: 11px; font-weight: 700; letter-spacing: .055em; text-transform: uppercase; color: rgb(107 114 128);">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($installedPlugins as $plugin)
                                <tr wire:key="installed-plugin-{{ $plugin->plugin_id }}">
                                    <td style="padding: 16px; vertical-align: top; border-bottom: 1px solid rgb(229 231 235); overflow-wrap: anywhere;">
                                        <div style="font-size: 13px; font-weight: 600; line-height: 1.5; color: rgb(17 24 39);">{{ $plugin->name }}</div>
                                        <div style="margin-top: 5px; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 11px; line-height: 1.5; color: rgb(107 114 128);">{{ $plugin->plugin_id }}</div>
                                    </td>
                                    <td style="padding: 16px; vertical-align: top; border-bottom: 1px solid rgb(229 231 235); font-size: 13px; line-height: 1.6; color: rgb(75 85 99);">{{ $plugin->type === 'ui-plugin' ? 'UI Plugin' : 'Feature Module' }}</td>
                                    <td style="padding: 16px; vertical-align: top; border-bottom: 1px solid rgb(229 231 235); font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12px; line-height: 1.6; white-space: nowrap; color: rgb(75 85 99);">v{{ $plugin->version }}</td>
                                    <td style="padding: 16px; vertical-align: top; border-bottom: 1px solid rgb(229 231 235);">
                                        @if ($plugin->is_enabled)
                                            <x-filament::badge color="success">Enabled</x-filament::badge>
                                        @else
                                            <x-filament::badge color="gray">Disabled</x-filament::badge>
                                        @endif
                                    </td>
                                    <td style="padding: 16px; vertical-align: top; border-bottom: 1px solid rgb(229 231 235); font-size: 13px; line-height: 1.65; overflow-wrap: anywhere; color: rgb(75 85 99);">{{ $plugin->description ?: 'No description provided.' }}</td>
                                    <td style="padding: 16px; vertical-align: top; border-bottom: 1px solid rgb(229 231 235);">
                                        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
                                            <x-filament::button size="sm" color="gray" wire:click.prevent="togglePlugin('{{ $plugin->plugin_id }}')" wire:loading.attr="disabled" wire:target="togglePlugin('{{ $plugin->plugin_id }}')">{{ $plugin->is_enabled ? 'Disable' : 'Enable' }}</x-filament::button>
                                            @if (! $plugin->is_enabled && ($rollbackAvailable[$plugin->plugin_id] ?? false))
                                                <x-filament::button size="sm" color="warning" wire:click.prevent="rollbackPlugin('{{ $plugin->plugin_id }}')" wire:confirm="Restore the previous version? The plugin will remain disabled after rollback." wire:loading.attr="disabled" wire:target="rollbackPlugin('{{ $plugin->plugin_id }}')">Rollback</x-filament::button>
                                            @endif
                                            <x-filament::button size="sm" color="danger" wire:click.prevent="uninstallPlugin('{{ $plugin->plugin_id }}')" wire:confirm="Uninstall this plugin? Its package files and registry entry will be removed. Business data is not automatically deleted. Rollback snapshots are kept separately." wire:loading.attr="disabled" wire:target="uninstallPlugin('{{ $plugin->plugin_id }}')">Uninstall</x-filament::button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p style="margin: 10px 0 0; font-size: 12px; line-height: 1.6; color: rgb(107 114 128);">On narrow screens, scroll the table horizontally to view all columns.</p>
            @endif
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Security &amp; Package Limitations</x-slot>
            <p style="margin: 0; max-width: 900px; font-size: 13px; line-height: 1.75; color: rgb(107 114 128);">
                Plugin Blade views and route files can execute server-side code. Install packages only from trusted sources. Disabled plugin route files are not loaded during normal route registration. Updates require a newer version, create a file snapshot, and leave the plugin disabled. Rollback restores the latest snapshot and also leaves the plugin disabled. This installer does not run plugin-specific database migrations or automatically delete application data when a package is uninstalled. Rollback snapshots are stored under storage/app/aion-plugin-backups and should be included in your backup policy.
            </p>
        </x-filament::section>
    </div>
</x-filament-panels::page>
