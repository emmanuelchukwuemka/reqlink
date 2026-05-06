<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Info')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        Select::make('role')
                            ->options([
                                'admin' => 'Admin',
                                'civilian' => 'Civilian',
                                'responder' => 'Responder',
                                'hospital' => 'Hospital Admin',
                            ])
                            ->required(),
                        Toggle::make('is_verified')
                            ->label('Account Verified'),
                    ]),

                Section::make('Medical Information')
                    ->columns(2)
                    ->schema([
                        Select::make('blood_group')
                            ->options([
                                'A+' => 'A+', 'A-' => 'A-',
                                'B+' => 'B+', 'B-' => 'B-',
                                'O+' => 'O+', 'O-' => 'O-',
                                'AB+' => 'AB+', 'AB-' => 'AB-',
                            ]),
                        TextInput::make('allergies')
                            ->placeholder('e.g. Peanuts, Penicillin'),
                        TextInput::make('medical_conditions')
                            ->placeholder('e.g. Asthma, Diabetes')
                            ->columnSpanFull(),
                    ]),

                Section::make('Emergency Contact')
                    ->columns(2)
                    ->schema([
                        TextInput::make('emergency_contact_name'),
                        TextInput::make('emergency_contact_phone')
                            ->tel(),
                    ]),

                Section::make('Verification Documents')
                    ->description('Professional licenses and business registration documents.')
                    ->columns(2)
                    ->schema([
                        \Filament\Forms\Components\FileUpload::make('license_path')
                            ->label('Professional License')
                            ->directory('partner_docs')
                            ->visibility('public')
                            ->openable()
                            ->downloadable(),
                        \Filament\Forms\Components\FileUpload::make('additional_docs_path')
                            ->label('Additional Documents')
                            ->directory('partner_docs')
                            ->visibility('public')
                            ->openable()
                            ->downloadable(),
                    ])
                    ->visible(fn ($record) => $record && $record->role !== 'civilian'),
            ]);
    }
}
