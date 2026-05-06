<?php

namespace App\Filament\Resources\Emergencies\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmergencyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Core Information')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required(),
                        Select::make('emergency_type_id')
                            ->relationship('emergencyType', 'name')
                            ->required(),
                        TextInput::make('subtype')
                            ->maxLength(255),
                        Select::make('priority')
                            ->options([
                                1 => 'Low',
                                2 => 'Medium',
                                3 => 'High',
                                4 => 'Critical',
                                5 => 'Immediate',
                            ])
                            ->required(),
                        Textarea::make('description')
                            ->columnSpanFull(),
                    ]),

                Section::make('Location Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('latitude')
                            ->numeric()
                            ->required(),
                        TextInput::make('longitude')
                            ->numeric()
                            ->required(),
                        TextInput::make('address')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),

                Section::make('Dispatch & Resolution')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'dispatched' => 'Dispatched',
                                'enroute' => 'Enroute',
                                'arrived' => 'Arrived',
                                'resolved' => 'Resolved',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required(),
                        Select::make('assigned_responder_id')
                            ->relationship('assignedResponder', 'id') // Using ID since responders might not have names directly
                            ->searchable(),
                        TextInput::make('eta_minutes')
                            ->numeric()
                            ->suffix('min'),
                        DateTimePicker::make('resolved_at'),
                    ]),
            ]);
    }
}
