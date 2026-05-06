<?php

namespace App\Filament\Resources\SecurityResource\Pages;

use App\Filament\Resources\SecurityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSecurities extends ListRecords
{
    protected static string $resource = SecurityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['responder_type'] = 'security';
                    return $data;
                }),
        ];
    }
}
