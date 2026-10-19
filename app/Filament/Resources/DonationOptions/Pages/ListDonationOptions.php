<?php

namespace App\Filament\Resources\DonationOptions\Pages;

use App\Filament\Resources\DonationOptions\DonationOptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDonationOptions extends ListRecords
{
    protected static string $resource = DonationOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
