<?php

namespace App\Filament\Resources;

use App\Domains\Responders\Models\Responder;
use App\Filament\Resources\AmbulanceResource\Pages;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AmbulanceResource extends Resource
{
    protected static ?string $model = Responder::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-truck';
    
    protected static string|null $navigationLabel = 'Ambulance Units';
    
    protected static string|\UnitEnum|null $navigationGroup = 'Emergency Services';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('responder_type', 'ambulance');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('vehicle_reg')
                    ->label('Ambulance Number'),
                Toggle::make('is_available')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Unit Name')->searchable(),
                Tables\Columns\TextColumn::make('vehicle_reg')->label('Vehicle'),
                Tables\Columns\IconColumn::make('is_available')->boolean(),
                Tables\Columns\TextColumn::make('is_on_duty')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->label('Duty Status'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAmbulances::route('/'),
        ];
    }
}
