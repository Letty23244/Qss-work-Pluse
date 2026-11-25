<?php

namespace App\Filament\Pages;

use App\Support\PerformanceReportPage;

class DailyPerformance extends PerformanceReportPage
{
    protected static ?string $title = 'Daily Performance';
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?int $navigationSort = 1;
    protected static string $groupBy = 'date';
}
