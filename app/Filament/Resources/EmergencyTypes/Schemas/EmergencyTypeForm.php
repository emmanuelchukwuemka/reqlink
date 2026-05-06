<?php

namespace App\Filament\Resources\EmergencyTypes\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmergencyTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('icon')
                    ->placeholder('heroicon-o-fire')
                    ->helperText('The Heroicon name for this emergency type.'),
                Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }
}
