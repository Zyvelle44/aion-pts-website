<?php

namespace App\Filament\Resources\News\Pages;

use App\Filament\Resources\News\NewsResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\URL;

class EditNews extends EditRecord
{
    protected static string $resource = NewsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Preview News')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->url(
                    fn () => URL::temporarySignedRoute(
                        'news.preview',
                        now()->addMinutes(30),
                        ['news' => $this->record]
                    )
                )
                ->openUrlInNewTab(),

            DeleteAction::make(),
        ];
    }
}
