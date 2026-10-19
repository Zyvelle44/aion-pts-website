<?php

namespace App\Filament\Resources\DonationOptions;

use App\Filament\Resources\DonationOptions\Pages\CreateDonationOption;
use App\Filament\Resources\DonationOptions\Pages\EditDonationOption;
use App\Filament\Resources\DonationOptions\Pages\ListDonationOptions;
use App\Models\DonationOption;
use BackedEnum;
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

class DonationOptionResource extends Resource
{
    protected static ?string $model = DonationOption::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static ?string $navigationLabel = 'Donation Options';

    protected static ?string $modelLabel = 'Donation Option';

    protected static ?string $pluralModelLabel = 'Donation Options';

    protected static string|\UnitEnum|null $navigationGroup = 'Website Content';

    protected static ?int $navigationSort = 5;

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
            Section::make('Donation Option')
                ->description('Configure a donation method for display. A payment URL is optional; no payment is processed by this feature.')
                ->schema([
                    TextInput::make('title')
                        ->label('Display Title')
                        ->required()
                        ->maxLength(180),

                    TextInput::make('method')
                        ->label('Method / Provider')
                        ->required()
                        ->maxLength(100)
                        ->helperText('Enter only a method you have actually configured.'),

                    Textarea::make('description')
                        ->rows(3)
                        ->maxLength(5000)
                        ->columnSpanFull(),

                    Textarea::make('instructions')
                        ->label('Payment Instructions')
                        ->rows(5)
                        ->maxLength(10000)
                        ->helperText('Only publish verified instructions. Do not enter private credentials or secret keys.')
                        ->columnSpanFull(),

                    TextInput::make('payment_url')
                        ->label('Official Payment URL')
                        ->url()
                        ->maxLength(2048)
                        ->nullable()
                        ->helperText('Optional. If empty, the public page will not show a payment button.'),
                ]),

            Section::make('Publication')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('sort_order')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->required()
                            ->default(0),

                        Toggle::make('is_active')
                            ->label('Show on Donation page')
                            ->default(false),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable()->wrap(),
                TextColumn::make('method')->badge()->sortable(),
                IconColumn::make('payment_url')
                    ->label('Payment URL Set')
                    ->state(fn (DonationOption $record): bool => filled($record->payment_url))
                    ->boolean(),
                ToggleColumn::make('is_active')->label('Active'),
                TextColumn::make('sort_order')->numeric()->sortable(),
                TextColumn::make('updated_at')->dateTime('d M Y H:i')->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('Publication')
                    ->options(['1' => 'Active', '0' => 'Hidden']),
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
            'index' => ListDonationOptions::route('/'),
            'create' => CreateDonationOption::route('/create'),
            'edit' => EditDonationOption::route('/{record}/edit'),
        ];
    }
}
