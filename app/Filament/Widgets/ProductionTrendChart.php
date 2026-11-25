<?php

namespace App\Filament\Widgets;

use App\Services\PerformanceService;
use Filament\Widgets\ChartWidget;

class ProductionTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Last 14 days: production vs target';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $rows = collect(app(PerformanceService::class)->breakdown('date', [
            'from' => today()->subDays(13)->toDateString(),
            'to'   => today()->toDateString(),
        ]))->keyBy('key');

        $labels = $produced = $target = [];
        for ($i = 13; $i >= 0; $i--) {
            $d = today()->subDays($i)->toDateString();
            $labels[]   = today()->subDays($i)->format('d M');
            $produced[] = $rows[$d]['produced'] ?? 0;
            $target[]   = $rows[$d]['target'] ?? 0;
        }

        return [
            'datasets' => [
                ['label' => 'Produced', 'data' => $produced, 'borderColor' => '#0d9488', 'backgroundColor' => '#0d9488'],
                ['label' => 'Target',   'data' => $target,   'borderColor' => '#f97316', 'backgroundColor' => '#f97316', 'borderDash' => [6, 4]],
            ],
            'labels' => $labels,
        ];
    }
}
