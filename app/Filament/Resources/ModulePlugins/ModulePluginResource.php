<?php

namespace App\Filament\Resources\ModulePlugins;

use App\Filament\Resources\ModulePlugins\Pages\CreateModulePlugin;
use App\Filament\Resources\ModulePlugins\Pages\EditModulePlugin;
use App\Filament\Resources\ModulePlugins\Pages\ListModulePlugins;
use App\Models\ModulePlugin;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ModulePluginResource extends Resource
{
    protected static ?string $model = ModulePlugin::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPuzzlePiece;
    protected static ?string $navigationLabel = 'Modules & Plugins';
    protected static ?string $modelLabel = 'Module / Plugin';
    protected static ?string $pluralModelLabel = 'Modules & Plugins';
    protected static string|\UnitEnum|null $navigationGroup = 'System Management';
    protected static ?int $navigationSort = 20;

    public static function canViewAny(): bool { return auth()->user()?->hasRole('super_admin') ?? false; }
    public static function canCreate(): bool { return static::canViewAny(); }
    public static function canEdit($record): bool { return static::canViewAny(); }
    public static function canDelete($record): bool { return static::canViewAny(); }
    public static function canDeleteAny(): bool { return static::canViewAny(); }

    public static function builtInFeatureKeys(): array
    {
        return ['home', 'news', 'info', 'download', 'donation', 'support', 'account.registration', 'account.login', 'account.characters', 'account.password', 'server.status'];
    }

    public static function isBuiltInFeatureKey(?string $key): bool
    {
        return is_string($key) && in_array($key, static::builtInFeatureKeys(), true);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Module / Plugin Identity')
                ->description('Register application features. New registry entries require code integration before activation.')
                ->schema([
                    TextInput::make('name')->required()->maxLength(120),
                    Select::make('type')->options(['module' => 'Module', 'plugin' => 'Plugin'])->required()->default('module'),
                    Grid::make(2)->schema([
                        TextInput::make('slug')->required()->maxLength(140)->unique(ignoreRecord: true)->dehydrateStateUsing(fn (?string $state): string => Str::slug((string) $state)),
                        TextInput::make('feature_key')->required()->maxLength(140)->unique(ignoreRecord: true)->rules(['regex:/^[a-z0-9]+(?:[._-][a-z0-9]+)*$/'])->helperText('Exact key used by middleware, e.g. news or account.characters.'),
                    ]),
                    Grid::make(2)->schema([
                        TextInput::make('version')->maxLength(40)->nullable()->placeholder('1.0.0'),
                        TextInput::make('sort_order')->numeric()->integer()->minValue(0)->required()->default(0),
                    ]),
                    Textarea::make('description')->rows(3)->maxLength(4000)->columnSpanFull(),
                ]),
            Section::make('Dependencies & Compatibility')
                ->description('Dependencies must reference existing feature keys. Version constraints use semantic version comparison against the configured AION PTS version.')
                ->schema([
                    TagsInput::make('dependencies')->label('Required feature keys')->placeholder('Add dependency key, e.g. server.status')->nestedRecursiveRules(['string', 'max:140', 'regex:/^[a-z0-9]+(?:[._-][a-z0-9]+)*$/'])->helperText('Leave empty if this feature has no dependencies.'),
                    Grid::make(2)->schema([
                        TextInput::make('min_app_version')->label('Minimum AION PTS version')->placeholder('1.0.0')->maxLength(40)->rules(['nullable', 'regex:/^\\d+\\.\\d+\\.\\d+(?:[-+][0-9A-Za-z.-]+)?$/']),
                        TextInput::make('max_app_version')->label('Maximum AION PTS version')->placeholder('2.0.0')->maxLength(40)->rules(['nullable', 'regex:/^\\d+\\.\\d+\\.\\d+(?:[-+][0-9A-Za-z.-]+)?$/']),
                    ]),
                    Toggle::make('is_enabled')->label('Enabled')->default(false)
                        ->disabled(fn (?ModulePlugin $record): bool => $record !== null && ! $record->is_enabled && (! static::isBuiltInFeatureKey($record->feature_key) || ! $record->activationReadiness()['ready']))
                        ->helperText('Only integrated features with satisfied dependencies and compatible versions can be enabled.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable()->description(fn (ModulePlugin $record): string => (string) $record->feature_key),
            TextColumn::make('type')->badge()->color(fn (string $state): string => $state === 'plugin' ? 'info' : 'gray')->formatStateUsing(fn (string $state): string => ucfirst($state)),
            TextColumn::make('integration_status')->label('Integration')->state(fn (ModulePlugin $record): string => static::isBuiltInFeatureKey($record->feature_key) ? 'Built-in' : 'Registry only')->badge()->color(fn (ModulePlugin $record): string => static::isBuiltInFeatureKey($record->feature_key) ? 'success' : 'warning'),
            TextColumn::make('readiness')->label('Activation readiness')->state(fn (ModulePlugin $record): string => $record->activationReadiness()['ready'] ? 'Ready' : 'Blocked')->badge()->color(fn (ModulePlugin $record): string => $record->activationReadiness()['ready'] ? 'success' : 'danger')->description(fn (ModulePlugin $record): string => implode(' ', $record->activationReadiness()['reasons']) ?: 'Dependencies and version constraints satisfied.'),
            TextColumn::make('version')->placeholder('—'),
            TextColumn::make('dependencies')->label('Dependencies')->state(fn (ModulePlugin $record): string => implode(', ', is_array($record->dependencies) ? $record->dependencies : []))->placeholder('None'),
            ToggleColumn::make('is_enabled')->label('Enabled')->disabled(fn (ModulePlugin $record): bool => ! $record->is_enabled && (! static::isBuiltInFeatureKey($record->feature_key) || ! $record->activationReadiness()['ready'])),
            TextColumn::make('sort_order')->numeric()->sortable(),
            TextColumn::make('updated_at')->dateTime('d M Y H:i')->sortable(),
        ])->filters([
            SelectFilter::make('type')->options(['module' => 'Module', 'plugin' => 'Plugin']),
            SelectFilter::make('is_enabled')->label('Status')->options(['1' => 'Enabled', '0' => 'Disabled']),
        ])->defaultSort('sort_order')->reorderable('sort_order')->recordActions([
            \Filament\Actions\EditAction::make(),
            \Filament\Actions\DeleteAction::make(),
        ])->toolbarActions([
            \Filament\Actions\BulkActionGroup::make([\Filament\Actions\DeleteBulkAction::make()]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListModulePlugins::route('/'), 'create' => CreateModulePlugin::route('/create'), 'edit' => EditModulePlugin::route('/{record}/edit')];
    }
}
