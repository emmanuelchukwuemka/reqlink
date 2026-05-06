<?php

namespace App\Filament\Resources\EmergencyTypes\Pages;

use App\Filament\Resources\EmergencyTypes\EmergencyTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmergencyType extends CreateRecord
{
    protected static string $resource = EmergencyTypeResource::class;
}
