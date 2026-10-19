<?php

namespace App\Filament\Resources\News\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Actions\ViewAction;

class NewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('Cover')
                    ->disk('public')
                    ->square()
                    ->size(64),

                TextColumn::make('title')
                    ->label('News Title')
                    ->searchable()
                    ->sortable()
                    ->limit(30, '...')
                    ->tooltip(fn($record): ?string => $record->title)
                    ->wrap(false)
                    ->description(fn($record): string => \Illuminate\Support\Str::limit(
                        $record->excerpt ?? '',
                        45,
                        '...'
                    )),

                TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'Announcement' => 'info',
                        'Update' => 'success',
                        'Event' => 'warning',
                        'Maintenance' => 'danger',
                        'Community' => 'gray',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(
                        fn(?string $state): string =>
                        match ($state) {
                            'published' => 'Published',
                            'draft' => 'Draft',
                            'scheduled' => 'Scheduled',
                            default => ucfirst($state ?? 'Unknown'),
                        }
                    )
                    ->color(fn(?string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'gray',
                        'scheduled' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label('Publication Date')
                    ->dateTime('d M Y, H:i')
                    ->placeholder('Not set')
                    ->sortable()
                    ->color(
                        fn($record): string =>
                        $record->status === 'published'
                            ? 'success'
                            : 'gray'
                    ),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Publication Status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'scheduled' => 'Scheduled',
                    ]),

                SelectFilter::make('category')
                    ->label('Category')
                    ->options([
                        'Announcement' => 'Announcement',
                        'Update' => 'Update',
                        'Event' => 'Event',
                        'Maintenance' => 'Maintenance',
                        'Community' => 'Community',
                    ]),

                SelectFilter::make('is_featured')
                    ->label('Featured Article')
                    ->options([
                        '1' => 'Featured',
                        '0' => 'Not Featured',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square'),
                ViewAction::make()
                    ->label('View'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(
                            fn(): bool =>
                            auth()->user()?->can('Delete:News') ?? false
                        )
                        ->before(function (): void {
                            abort_unless(
                                auth()->user()?->can('Delete:News') ?? false,
                                403
                            );
                        })
                        ->label('Delete Selected')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation(),
                ]),
            ]);
    }
}
