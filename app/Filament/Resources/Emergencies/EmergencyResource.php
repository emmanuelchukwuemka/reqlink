<?php

namespace App\Filament\Resources\Emergencies;

use App\Domains\Emergencies\Models\Emergency;
use App\Filament\Resources\Emergencies\Pages\CreateEmergency;
use App\Filament\Resources\Emergencies\Pages\EditEmergency;
use App\Filament\Resources\Emergencies\Pages\ListEmergencies;
use App\Filament\Resources\Emergencies\Schemas\EmergencyForm;
use App\Filament\Resources\Emergencies\Tables\EmergenciesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EmergencyResource extends Resource
{
    protected static ?string $model = Emergency::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return EmergencyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmergenciesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmergencies::route('/'),
            'create' => CreateEmergency::route('/create'),
            'edit' => EditEmergency::route('/{record}/edit'),
        ];
    }
}
