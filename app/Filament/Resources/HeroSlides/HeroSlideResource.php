<?php

namespace App\Filament\Resources\HeroSlides;

use App\Filament\Resources\HeroSlides\Pages\CreateHeroSlide;
use App\Filament\Resources\HeroSlides\Pages\EditHeroSlide;
use App\Filament\Resources\HeroSlides\Pages\ListHeroSlides;
use App\Models\HeroSlide;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class HeroSlideResource extends Resource
{
    protected static ?string $model = HeroSlide::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Hero Slider';

    protected static ?string $modelLabel = 'Hero Slide';

    protected static ?string $pluralModelLabel = 'Hero Slides';

    protected static string|\UnitEnum|null $navigationGroup = 'Website Content';

    protected static ?int $navigationSort = 2;

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
            Section::make('Hero Content')
                ->description('Edit the headline and text shown on the public homepage.')
                ->schema([
                    TextInput::make('eyebrow')
                        ->label('Small Kicker')
                        ->required()
                        ->maxLength(120)
                        ->default('A WORLD BEYOND THE SKY')
                        ->columnSpanFull(),

                    Grid::make(2)->schema([
                        TextInput::make('title')
                            ->label('Main Heading')
                            ->required()
                            ->maxLength(120)
                            ->helperText('Keep this concise for a premium layout.'),

                        TextInput::make('highlight')
                            ->label('Gold Highlight')
                            ->maxLength(120)
                            ->helperText('Optional second line, displayed in gold.'),
                    ]),

                    Textarea::make('description')
                        ->label('Description')
                        ->required()
                        ->rows(3)
                        ->maxLength(1000)
                        ->columnSpanFull(),
                ])
                ->columns(1),

            Section::make('Hero Artwork')
                ->description('Upload a landscape image. Wide images around 1600 × 900 work best.')
                ->schema([
                    FileUpload::make('image_path')
                        ->label('Hero Background Image')
                        ->image()
                        ->disk('public')
                        ->directory('hero-slides')
                        ->visibility('public')
                        ->imageEditor()
                        ->maxSize(8192)
                        ->required()
                        ->columnSpanFull(),
                ]),

            Section::make('Hero Buttons')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('primary_label')
                            ->label('Primary Button Text')
                            ->required()
                            ->maxLength(80)
                            ->default('EXPLORE DOWNLOADS'),

                        TextInput::make('primary_url')
                            ->label('Primary Button URL')
                            ->required()
                            ->maxLength(2048)
                            ->default('/download'),

                        TextInput::make('secondary_label')
                            ->label('Secondary Button Text')
                            ->required()
                            ->maxLength(80)
                            ->default('LATEST CHRONICLES'),

                        TextInput::make('secondary_url')
                            ->label('Secondary Button URL')
                            ->required()
                            ->maxLength(2048)
                            ->default('/news'),
                    ]),
                ]),

            Section::make('Slider Settings')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('sort_order')
                            ->label('Display Order')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->required()
                            ->default(0),

                        Toggle::make('is_active')
                            ->label('Active on Homepage')
                            ->default(true)
                            ->helperText('Inactive slides will not be shown publicly.'),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Artwork')
                    ->disk('public')
                    ->height(54)
                    ->width(96),

                TextColumn::make('title')
                    ->label('Heading')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('highlight')
                    ->label('Gold Text')
                    ->placeholder('—'),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->numeric()
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('Active'),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
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
            'index' => ListHeroSlides::route('/'),
            'create' => CreateHeroSlide::route('/create'),
            'edit' => EditHeroSlide::route('/{record}/edit'),
        ];
    }
}
