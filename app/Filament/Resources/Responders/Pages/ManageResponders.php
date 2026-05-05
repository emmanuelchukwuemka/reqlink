<?php

namespace App\Filament\Resources\Responders\Pages;

use App\Filament\Resources\Responders\ResponderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageResponders extends ManageRecords
{
    protected static string $resource = ResponderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
