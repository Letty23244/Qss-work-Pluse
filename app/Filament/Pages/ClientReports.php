<?php

namespace App\Filament\Pages;

use App\Support\PerformanceReportPage;

class ClientReports extends PerformanceReportPage
{
    protected static ?string $title = 'Client Reports';
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?int $navigationSort = 4;
    protected static string $groupBy = 'client_id';
}
