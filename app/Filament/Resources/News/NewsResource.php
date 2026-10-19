<?php

namespace App\Filament\Resources\News;

use App\Filament\Resources\News\Pages\CreateNews;
use App\Filament\Resources\News\Pages\EditNews;
use App\Filament\Resources\News\Pages\ListNews;
use App\Filament\Resources\News\Pages\ViewNews;
use App\Filament\Resources\News\Schemas\NewsForm;
use App\Filament\Resources\News\Tables\NewsTable;
use App\Models\News;
use App\Support\NewsHtml;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Illuminate\Support\HtmlString;

class NewsResource extends Resource
{
    protected static ?string $model = News::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'News';

    protected static string|\UnitEnum|null $navigationGroup = 'Website Content';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return NewsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NewsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNews::route('/'),
            'create' => CreateNews::route('/create'),
            'edit' => EditNews::route('/{record}/edit'),
            'view' => ViewNews::route('/{record}'),
        ];
    }


    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('ARTICLE HEADER')
                ->description('News article overview and publication details.')
                ->schema([
                    TextEntry::make('title')
                        ->label('Article Title')
                        ->columnSpanFull(),

                    TextEntry::make('category')
                        ->label('Category')
                        ->badge(),

                    TextEntry::make('status')
                        ->label('Publication Status')
                        ->badge()
                        ->color(fn(?string $state): string => match ($state) {
                            'published' => 'success',
                            'draft' => 'gray',
                            default => 'warning',
                        }),

                    TextEntry::make('published_at')
                        ->label('Published At')
                        ->dateTime('d M Y, H:i')
                        ->placeholder('Not published yet'),

                    TextEntry::make('slug')
                        ->label('Article Slug')
                        ->copyable()
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull(),

            Section::make('ARTICLE SUMMARY')
                ->description('A short introduction displayed before the full article.')
                ->schema([
                    TextEntry::make('excerpt')
                        ->label('Excerpt')
                        ->placeholder('No excerpt available.')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),

            Section::make('ARTICLE CONTENT')
                ->description('Full article content with formatted paragraphs and headings.')
                ->schema([
                    TextEntry::make('body')
                        ->hiddenLabel()
                        ->formatStateUsing(function ($state) {
                            $body = (string) ($state ?? '');

                            // Sanitize HTML articles before rendering.
                            if ($body !== strip_tags($body)) {
                                return new HtmlString(
                                    NewsHtml::sanitize($body)
                                );
                            }

                            // Escape plain text before converting it into paragraphs.
                            $paragraphs = preg_split(
                                '/\R\s*\R/u',
                                trim($body)
                            ) ?: [];

                            $html = collect($paragraphs)
                                ->filter(
                                    fn($paragraph) => trim($paragraph) !== ''
                                )
                                ->map(
                                    fn($paragraph) => '<p>'
                                        . nl2br(e(trim($paragraph)))
                                        . '</p>'
                                )
                                ->implode('');

                            return new HtmlString($html);
                        })
                        ->html()
                        ->prose()
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),

            Section::make('SEO INFORMATION')
                ->description('Search engine metadata for this article.')
                ->schema([
                    TextEntry::make('meta_title')
                        ->label('Meta Title')
                        ->placeholder('Not configured')
                        ->columnSpanFull(),

                    TextEntry::make('meta_description')
                        ->label('Meta Description')
                        ->placeholder('Not configured')
                        ->columnSpanFull(),
                ])
                ->columns(1)
                ->collapsed()
                ->columnSpanFull(),
        ]);
    }
}
