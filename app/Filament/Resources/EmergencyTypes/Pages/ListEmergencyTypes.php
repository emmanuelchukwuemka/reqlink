<?php

namespace App\Filament\Resources\EmergencyTypes\Pages;

use App\Filament\Resources\EmergencyTypes\EmergencyTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmergencyTypes extends ListRecords
{
    protected static string $resource = EmergencyTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
