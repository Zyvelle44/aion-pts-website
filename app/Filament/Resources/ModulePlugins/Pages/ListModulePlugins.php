<?php
namespace App\Filament\Resources\ModulePlugins\Pages;
use App\Filament\Resources\ModulePlugins\ModulePluginResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListModulePlugins extends ListRecords
{
    protected static string $resource = ModulePluginResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
