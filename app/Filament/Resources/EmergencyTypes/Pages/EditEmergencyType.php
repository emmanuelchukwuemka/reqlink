<?php

namespace App\Filament\Resources\EmergencyTypes\Pages;

use App\Filament\Resources\EmergencyTypes\EmergencyTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmergencyType extends EditRecord
{
    protected static string $resource = EmergencyTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
