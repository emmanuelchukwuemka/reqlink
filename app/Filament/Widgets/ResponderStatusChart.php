<?php

namespace App\Filament\Widgets;

use App\Domains\Responders\Models\Responder;
use Filament\Widgets\ChartWidget;

class ResponderStatusChart extends ChartWidget
{
    protected ?string $heading = 'Responder Availability Status';
    
    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $available = Responder::where('is_available', true)->count();
        $busy = Responder::where('is_available', false)->count();

        return [
            'datasets' => [
                [
                    'label' => 'Responders',
                    'data' => [$available, $busy],
                    'backgroundColor' => [
                        '#10b981', // Green for available
                        '#f59e0b', // Amber for busy
                    ],
                    'hoverOffset' => 4
                ],
            ],
            'labels' => ['Available', 'Busy / On Mission'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
