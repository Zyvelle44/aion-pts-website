<?php

namespace App\Filament\Resources\News\Schemas;

use App\Models\News;
use App\Services\GeminiNewsWriter;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Filament\Forms\Components\RichEditor;
use Throwable;

class NewsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make([
                'default' => 1,
                'xl' => 2,
            ])
                ->schema([
                    Section::make('News Editor')
                        ->description('Create and edit your AION PTS news article.')
                        ->schema([
                            Textarea::make('ai_topic')
                                ->label('AI Writing Topic')
                                ->placeholder(
                                    'Example: Upcoming server maintenance, new events, or community announcements...'
                                )
                                ->helperText(
                                    'Enter a topic and use Generate with AI to create an English news draft.'
                                )
                                ->rows(3)
                                ->maxLength(2000)
                                ->dehydrated(false)
                                ->columnSpanFull(),

                            Actions::make([
                                Action::make('generateNews')
                                    ->label('Generate with AI')
                                    ->icon('heroicon-o-sparkles')
                                    ->color('primary')
                                    ->button()
                                    ->action(function (
                                        Get $get,
                                        Set $set,
                                        GeminiNewsWriter $writer
                                    ): void {
                                        $topic = trim(
                                            (string) ($get('ai_topic') ?? '')
                                        );

                                        if ($topic === '') {
                                            Notification::make()
                                                ->title('Topic is required')
                                                ->body(
                                                    'Enter a topic before generating a draft.'
                                                )
                                                ->warning()
                                                ->send();

                                            return;
                                        }

                                        $category = (string) (
                                            $get('category') ?: 'Announcement'
                                        );

                                        try {
                                            $draft = $writer->generate(
                                                $topic,
                                                $category
                                            );

                                            $originalSlug = Str::slug(
                                                $draft['slug']
                                            );

                                            $slug = $originalSlug;
                                            $counter = 2;

                                            while (
                                                News::query()
                                                ->where('slug', $slug)
                                                ->when(
                                                    $get('id'),
                                                    fn($query, $id) => $query
                                                        ->where('id', '!=', $id)
                                                )
                                                ->exists()
                                            ) {
                                                $suffix = '-' . $counter;

                                                $slug = Str::limit(
                                                    $originalSlug,
                                                    220 - strlen($suffix),
                                                    ''
                                                ) . $suffix;

                                                $counter++;
                                            }

                                            $set('title', $draft['title']);
                                            $set('slug', $slug);
                                            $set('category', $draft['category']);
                                            $set('excerpt', $draft['excerpt']);
                                            $set('body', $draft['body']);
                                            $set('meta_title', $draft['meta_title']);
                                            $set(
                                                'meta_description',
                                                $draft['meta_description']
                                            );

                                            // AI-generated articles remain drafts.
                                            $set('status', 'draft');
                                            $set('published_at', null);
                                            $set('is_featured', false);

                                            Notification::make()
                                                ->title('News draft generated')
                                                ->body(
                                                    'Review the content and live preview before saving.'
                                                )
                                                ->success()
                                                ->send();
                                        } catch (Throwable $exception) {
                                            report($exception);

                                            Notification::make()
                                                ->title('AI generation failed')
                                                ->body(
                                                    'Check your Gemini configuration, API access, and quota, then try again.'
                                                )
                                                ->danger()
                                                ->send();
                                        }
                                    }),
                            ])->columnSpanFull(),

                            Grid::make(2)
                                ->schema([
                                    TextInput::make('title')
                                        ->label('News Title')
                                        ->required()
                                        ->maxLength(200)
                                        ->live(debounce: 400)
                                        ->afterStateUpdated(function (
                                            ?string $state,
                                            Set $set,
                                            Get $get
                                        ): void {
                                            if (blank($get('slug')) && filled($state)) {
                                                $set('slug', Str::slug($state));
                                            }
                                        }),

                                    TextInput::make('slug')
                                        ->label('URL Slug')
                                        ->required()
                                        ->maxLength(220)
                                        ->unique(ignoreRecord: true)
                                        ->dehydrateStateUsing(
                                            fn(?string $state): string => Str::slug(
                                                $state ?? ''
                                            )
                                        ),

                                    Select::make('category')
                                        ->label('Category')
                                        ->options([
                                            'Announcement' => 'Announcement',
                                            'Update' => 'Update',
                                            'Event' => 'Event',
                                            'Maintenance' => 'Maintenance',
                                            'Community' => 'Community',
                                        ])
                                        ->default('Announcement')
                                        ->required()
                                        ->native(false)
                                        ->live(),

                                    Select::make('status')
                                        ->label('Publication Status')
                                        ->options([
                                            'draft' => 'Draft',
                                            'published' => 'Published',
                                        ])
                                        ->default('draft')
                                        ->required()
                                        ->native(false)
                                        ->live()
                                        ->afterStateUpdated(function (
                                            ?string $state,
                                            Get $get,
                                            Set $set
                                        ): void {
                                            if (
                                                $state === 'published'
                                                && blank($get('published_at'))
                                            ) {
                                                $set(
                                                    'published_at',
                                                    now()->format('Y-m-d H:i:s')
                                                );
                                            }
                                        }),

                                    Toggle::make('is_featured')
                                        ->label('Featured Article')
                                        ->helperText(
                                            'Display this article as a featured news item.'
                                        )
                                        ->default(false),
                                ])
                                ->columnSpanFull(),

                            Textarea::make('excerpt')
                                ->label('Excerpt')
                                ->placeholder(
                                    'Write a short summary of the article...'
                                )
                                ->rows(3)
                                ->maxLength(500)
                                ->live(debounce: 400)
                                ->columnSpanFull(),


                            RichEditor::make('body')
                                ->label('Article Body')
                                ->required()
                                ->toolbarButtons([
                                    ['bold', 'italic', 'underline', 'strike'],
                                    ['h1', 'h2', 'h3'],
                                    ['alignStart', 'alignCenter', 'alignEnd', 'alignJustify'],
                                    ['bulletList', 'orderedList'],
                                    ['link', 'blockquote'],
                                    ['table', 'attachFiles'],
                                    ['undo', 'redo'],
                                ])
                                ->fileAttachmentsDisk('public')
                                ->fileAttachmentsDirectory('news/article-images')
                                ->fileAttachmentsVisibility('public')
                                ->fileAttachmentsAcceptedFileTypes([
                                    'image/jpeg',
                                    'image/png',
                                    'image/webp',
                                ])
                                ->fileAttachmentsMaxSize(5120)
                                ->resizableImages()
                                ->columnSpanFull(),

                            FileUpload::make('cover_image')
                                ->label('Cover Image')
                                ->image()
                                ->disk('public')
                                ->directory('news')
                                ->visibility('public')
                                ->maxSize(5120)
                                ->imageEditor()
                                ->live()
                                ->columnSpanFull(),

                            DateTimePicker::make('published_at')
                                ->label('Publication Date')
                                ->helperText(
                                    'Choose the publication date and time.'
                                )
                                ->live(),

                            TextInput::make('meta_title')
                                ->label('SEO Title')
                                ->placeholder(
                                    'Enter an SEO-friendly page title...'
                                )
                                ->maxLength(70)
                                ->live(debounce: 400)
                                ->helperText(
                                    'Recommended: up to 60 characters. Maximum: 70 characters.'
                                )
                                ->columnSpanFull(),

                            Textarea::make('meta_description')
                                ->label('SEO Description')
                                ->placeholder(
                                    'Write a concise search engine description...'
                                )
                                ->rows(3)
                                ->maxLength(170)
                                ->live(debounce: 400)
                                ->helperText(
                                    'Recommended: 140–160 characters. Maximum: 170 characters.'
                                )
                                ->columnSpanFull(),
                        ])
                        ->columns(1)
                        ->columnSpan(1),

                    Section::make('Live Preview')
                        ->description(
                            'Changes appear here before you save the article.'
                        )
                        ->schema([
                            View::make('filament.news.live-preview')
                                ->viewData(
                                    fn(Get $get): array => NewsLivePreview::data($get)
                                ),
                        ])
                        ->columnSpan(1),
                ])
                ->columnSpanFull(),
        ]);
    }
}
