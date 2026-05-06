<?php

namespace App\Filament\Widgets;

use App\Domains\Users\Models\User;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestRegistrations extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()->latest()->limit(5)
            )
            ->columns([
                TextColumn::make('name')
                    ->description(fn (User $record): string => $record->email ?? '')
                    ->searchable(),
                TextColumn::make('phone'),
                TextColumn::make('role')
                    ->badge()
                    ->colors([
                        'danger' => 'admin',
                        'success' => ['responder', 'ambulance', 'fire', 'security', 'doctor'],
                        'warning' => 'hospital',
                        'secondary' => 'civilian',
                    ]),
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->formatStateUsing(fn ($state): string => $state ? $state->format('M j, Y') : ''),
            ])
            ->actions([
                Action::make('view')
                    ->url(fn (User $record): string => "/admin/users/{$record->id}/edit")
                    ->icon('heroicon-m-eye'),
            ]);
    }
}
