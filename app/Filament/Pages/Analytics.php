<?php

namespace App\Filament\Pages;

use App\Support\PerformanceReportPage;

class Analytics extends PerformanceReportPage
{
    protected static ?string $title = 'Analytics';
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?int $navigationSort = 5;
    protected static string $groupBy = 'work_assignment_id';
}
