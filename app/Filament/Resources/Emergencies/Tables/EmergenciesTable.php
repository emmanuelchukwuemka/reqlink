<?php

namespace App\Filament\Resources\Emergencies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmergenciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->formatStateUsing(fn ($state): string => (string) $state)
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Caller')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('emergencyType.name')
                    ->label('Type')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'danger' => 'pending',
                        'warning' => ['dispatched', 'enroute'],
                        'info' => 'arrived',
                        'success' => 'resolved',
                        'secondary' => 'cancelled',
                    ]),
                TextColumn::make('priority')
                    ->label('Priority')
                    ->badge()
                    ->colors([
                        'secondary' => 1,
                        'info' => 2,
                        'warning' => 3,
                        'danger' => [4, 5],
                    ])
                    ->formatStateUsing(fn ($state): string => match ((int) $state) {
                        1 => 'Low',
                        2 => 'Medium',
                        3 => 'High',
                        4 => 'Critical',
                        5 => 'Immediate',
                        default => 'Unknown',
                    }),
                TextColumn::make('created_at')
                    ->label('Reported At')
                    ->formatStateUsing(fn ($state): string => $state ? $state->format('M j, Y H:i') : '')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false)
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
