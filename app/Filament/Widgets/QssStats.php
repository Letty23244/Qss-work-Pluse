<?php

namespace App\Filament\Widgets;

use App\Models\Shift;
use App\Services\PerformanceService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class QssStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = today()->toDateString();
        $s = app(PerformanceService::class)->summary(['from' => $today, 'to' => $today]);

        return [
            Stat::make("Today's production", number_format($s['produced'], 2))
                ->description('Pending: '.number_format($s['pending'], 2)),
            Stat::make("Today's target", number_format($s['target'], 2))
                ->description('Remaining: '.number_format($s['remaining'], 2)),
            Stat::make('Target achievement', $s['achievement'] === null ? '—' : $s['achievement'].'%')
                ->color(($s['achievement'] ?? 0) >= 100 ? 'success' : 'warning'),
            Stat::make('Active shifts', Shift::where('status', 'active')->count())
                ->description(Shift::whereDate('date', $today)->count().' scheduled today'),
        ];
    }
}
