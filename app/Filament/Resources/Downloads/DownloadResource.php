<?php

namespace App\Filament\Resources\Downloads;

use App\Filament\Resources\Downloads\Pages\CreateDownload;
use App\Filament\Resources\Downloads\Pages\EditDownload;
use App\Filament\Resources\Downloads\Pages\ListDownloads;
use App\Models\DownloadItem;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DownloadResource extends Resource
{
    protected static ?string $model = DownloadItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownTray;

    protected static ?string $navigationLabel = 'Downloads';

    protected static ?string $modelLabel = 'Download Item';

    protected static ?string $pluralModelLabel = 'Download Center';

    protected static string|\UnitEnum|null $navigationGroup = 'Website Content';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit($record): bool
    {
        return static::canViewAny();
    }

    public static function canDelete($record): bool
    {
        return static::canViewAny();
    }

    public static function canDeleteAny(): bool
    {
        return static::canViewAny();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Download Identity')
                ->description('Configure a game client or manual patch. Leave the URL empty until an official source is available.')
                ->schema([
                    TextInput::make('title')
                        ->label('Display Title')
                        ->required()
                        ->maxLength(180),

                    TextInput::make('slug')
                        ->required()
                        ->maxLength(200)
                        ->unique(ignoreRecord: true)
                        ->helperText('Unique internal URL key. Use lowercase letters and hyphens.')
                        ->dehydrateStateUsing(fn (?string $state): string => Str::slug((string) $state)),

                    Grid::make(2)->schema([
                        Select::make('type')
                            ->options([
                                'game_client' => 'Game Client',
                                'manual_patch' => 'Manual Patch',
                            ])
                            ->required()
                            ->default('game_client'),

                        TextInput::make('version')
                            ->label('Version')
                            ->maxLength(80)
                            ->placeholder('Not confirmed'),
                    ]),

                    Textarea::make('description')
                        ->rows(3)
                        ->maxLength(5000)
                        ->columnSpanFull(),
                ]),

            Section::make('File Source & Verification')
                ->schema([
                    TextInput::make('download_url')
                        ->label('Official Download URL')
                        ->url()
                        ->maxLength(2048)
                        ->nullable()
                        ->helperText('Optional. The public download button remains disabled while this field is empty.'),

                    Grid::make(2)->schema([
                        TextInput::make('file_size_bytes')
                            ->label('File Size (bytes)')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->nullable(),

                        TextInput::make('sha256')
                            ->label('SHA-256 Checksum')
                            ->maxLength(64)
                            ->nullable()
                            ->rules(['nullable', 'regex:/^[a-fA-F0-9]{64}$/'])
                            ->helperText('Optional 64-character SHA-256 hash.'),
                    ]),

                    Textarea::make('release_notes')
                        ->label('Release Notes')
                        ->rows(4)
                        ->maxLength(10000)
                        ->columnSpanFull(),
                ]),

            Section::make('Publication')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('sort_order')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->required()
                            ->default(0),

                        Toggle::make('is_active')
                            ->label('Active on Download Center')
                            ->default(false),

                        DateTimePicker::make('published_at')
                            ->label('Publish At')
                            ->seconds(false)
                            ->nullable(),
                    ]),
                    \Filament\Forms\Components\Placeholder::make('publication_note')
                        ->label('Publishing rule')
                        ->content('An item appears publicly only when Active is enabled and Publish At is empty or in the past.')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->description(fn (DownloadItem $record): string => $record->version ?: 'Version not set'),

                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'game_client' => 'Game Client',
                        'manual_patch' => 'Manual Patch',
                        default => ucfirst(str_replace('_', ' ', $state ?? 'unknown')),
                    })
                    ->color(fn (?string $state): string => $state === 'game_client' ? 'info' : 'warning')
                    ->sortable(),

                IconColumn::make('download_url')
                    ->label('URL Set')
                    ->state(fn (DownloadItem $record): bool => filled($record->download_url))
                    ->boolean(),

                ToggleColumn::make('is_active')
                    ->label('Active'),

                TextColumn::make('published_at')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Not scheduled')
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'game_client' => 'Game Client',
                        'manual_patch' => 'Manual Patch',
                    ]),
                SelectFilter::make('is_active')
                    ->label('Active')
                    ->options(['1' => 'Active', '0' => 'Inactive']),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->recordActions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDownloads::route('/'),
            'create' => CreateDownload::route('/create'),
            'edit' => EditDownload::route('/{record}/edit'),
        ];
    }
}
