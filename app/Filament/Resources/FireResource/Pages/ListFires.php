<?php

namespace App\Filament\Resources\FireResource\Pages;

use App\Filament\Resources\FireResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFires extends ListRecords
{
    protected static string $resource = FireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['responder_type'] = 'fire';
                    return $data;
                }),
        ];
    }
}
