<?php

namespace App\Filament\Resources\EmergencyTypes;

use App\Domains\Emergencies\Models\EmergencyType;
use App\Filament\Resources\EmergencyTypes\Pages\CreateEmergencyType;
use App\Filament\Resources\EmergencyTypes\Pages\EditEmergencyType;
use App\Filament\Resources\EmergencyTypes\Pages\ListEmergencyTypes;
use App\Filament\Resources\EmergencyTypes\Schemas\EmergencyTypeForm;
use App\Filament\Resources\EmergencyTypes\Tables\EmergencyTypesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EmergencyTypeResource extends Resource
{
    protected static ?string $model = EmergencyType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return EmergencyTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmergencyTypesTable::configure($table);
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
            'index' => ListEmergencyTypes::route('/'),
            'create' => CreateEmergencyType::route('/create'),
            'edit' => EditEmergencyType::route('/{record}/edit'),
        ];
    }
}
