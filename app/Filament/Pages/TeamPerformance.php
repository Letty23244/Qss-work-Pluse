<?php

namespace App\Filament\Pages;

use App\Support\PerformanceReportPage;

class TeamPerformance extends PerformanceReportPage
{
    protected static ?string $title = 'Team Performance';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 3;
    protected static string $groupBy = 'team_id';
}
