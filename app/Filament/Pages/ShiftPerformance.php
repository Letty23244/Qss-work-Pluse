<?php

namespace App\Filament\Pages;

use App\Support\PerformanceReportPage;

class ShiftPerformance extends PerformanceReportPage
{
    protected static ?string $title = 'Shift Performance';
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?int $navigationSort = 2;
    protected static string $groupBy = 'shift_type';
}
