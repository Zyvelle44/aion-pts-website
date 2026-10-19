<?php

namespace App\Filament\Resources\DonationOptions\Pages;

use App\Filament\Resources\DonationOptions\DonationOptionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDonationOption extends CreateRecord
{
    protected static string $resource = DonationOptionResource::class;
}
