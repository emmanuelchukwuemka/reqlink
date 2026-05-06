<?php

namespace App\Filament\Resources\Responders;

use App\Filament\Resources\Responders\Pages\ManageResponders;
use App\Domains\Responders\Models\Responder;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ResponderResource extends Resource
{
    protected static ?string $model = Responder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('responder_type')
                    ->options([
                        'ambulance' => 'Ambulance',
                        'security' => 'Security',
                        'fire' => 'Fire',
                        'doctor' => 'Doctor',
                    ])
                    ->required(),
                TextInput::make('vehicle_reg'),
                TextInput::make('capacity')
                    ->numeric()
                    ->default(1),
                Toggle::make('is_available')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable(),
                TextColumn::make('responder_type')
                    ->badge(),
                TextColumn::make('vehicle_reg'),
                IconColumn::make('is_available')
                    ->boolean(),
                TextColumn::make('last_ping')
                    ->formatStateUsing(fn ($state): string => $state ? $state->format('M j, Y H:i') : 'Never'),
            ])
            ->paginated(false)
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageResponders::route('/'),
        ];
    }
}
