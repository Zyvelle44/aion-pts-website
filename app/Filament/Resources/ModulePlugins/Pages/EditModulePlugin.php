<?php
namespace App\Filament\Resources\ModulePlugins\Pages;
use App\Filament\Resources\ModulePlugins\ModulePluginResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
class EditModulePlugin extends EditRecord
{
    protected static string $resource = ModulePluginResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
}
