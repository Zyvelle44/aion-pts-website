<?php

namespace App\Filament\Resources\SupportFaqs\Pages;

use App\Filament\Resources\SupportFaqs\SupportFaqResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSupportFaqs extends ListRecords
{
    protected static string $resource = SupportFaqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
