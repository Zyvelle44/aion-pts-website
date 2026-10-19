<?php

namespace App\Filament\Resources\SupportFaqs;

use App\Filament\Resources\SupportFaqs\Pages\CreateSupportFaq;
use App\Filament\Resources\SupportFaqs\Pages\EditSupportFaq;
use App\Filament\Resources\SupportFaqs\Pages\ListSupportFaqs;
use App\Models\SupportFaq;
use BackedEnum;
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

class SupportFaqResource extends Resource
{
    protected static ?string $model = SupportFaq::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLifebuoy;

    protected static ?string $navigationLabel = 'Support FAQ';

    protected static ?string $modelLabel = 'Support FAQ';

    protected static ?string $pluralModelLabel = 'Support FAQs';

    protected static string|\UnitEnum|null $navigationGroup = 'Website Content';

    protected static ?int $navigationSort = 4;

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
            Section::make('FAQ Content')
                ->description('Publish clear, verified answers for common player questions.')
                ->schema([
                    TextInput::make('question')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Select::make('category')
                        ->options([
                            'General' => 'General',
                            'Installation' => 'Installation',
                            'Account' => 'Account',
                            'Technical' => 'Technical',
                            'Gameplay' => 'Gameplay',
                        ])
                        ->searchable()
                        ->required()
                        ->default('General'),

                    Textarea::make('answer')
                        ->required()
                        ->rows(6)
                        ->maxLength(12000)
                        ->columnSpanFull(),
                ]),

            Section::make('Display Settings')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('sort_order')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->required()
                            ->default(0),

                        Toggle::make('is_active')
                            ->label('Show on Support page')
                            ->default(false),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('category')
                    ->badge()
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('Published'),

                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'General' => 'General',
                        'Installation' => 'Installation',
                        'Account' => 'Account',
                        'Technical' => 'Technical',
                        'Gameplay' => 'Gameplay',
                    ]),
                SelectFilter::make('is_active')
                    ->label('Publication')
                    ->options(['1' => 'Published', '0' => 'Hidden']),
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
            'index' => ListSupportFaqs::route('/'),
            'create' => CreateSupportFaq::route('/create'),
            'edit' => EditSupportFaq::route('/{record}/edit'),
        ];
    }
}
