<?php

namespace App\Filament\Widgets;

use App\Domains\Emergencies\Models\Emergency;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class EmergencyTrendChart extends ChartWidget
{
    protected ?string $heading = 'Emergency Volume (Last 7 Days)';
    
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $data = Emergency::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as aggregate')
            )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Emergencies',
                    'data' => $data->pluck('aggregate')->toArray(),
                    'fill' => 'start',
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $data->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
