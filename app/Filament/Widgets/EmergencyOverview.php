<?php

namespace App\Filament\Widgets;

use App\Domains\Emergencies\Models\Emergency;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EmergencyOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Pending Emergencies', (string) Emergency::where('status', 'pending')->count())
                ->description('Requires immediate dispatch')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger'),
            Stat::make('Active Incidents', (string) Emergency::whereIn('status', ['dispatched', 'enroute', 'arrived'])->count())
                ->description('Responders on field')
                ->descriptionIcon('heroicon-m-truck')
                ->color('warning'),
            Stat::make('Resolved Today', (string) Emergency::where('status', 'resolved')->whereDate('resolved_at', today())->count())
                ->description('Successfully handled')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
