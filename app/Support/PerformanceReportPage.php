<?php

namespace App\Support;

use App\Models\Client;
use App\Services\{PerformanceService, ReportExporter};
use Filament\Actions\Action;
use Filament\Pages\Page;

/** Shared behaviour for the Monitoring & Reports pages in the admin panel. */
abstract class PerformanceReportPage extends Page
{
    protected static string $view = 'filament.pages.performance-report';
    protected static ?string $navigationGroup = 'Monitoring & Reports';

    /** one of PerformanceService::GROUPS */
    protected static string $groupBy = 'date';

    public ?string $from = null;
    public ?string $to = null;
    public ?string $clientId = null;

    public function mount(): void
    {
        $this->from = today()->startOfMonth()->toDateString();
        $this->to   = today()->toDateString();
    }

    public function getFilters(): array
    {
        return ['client_id' => $this->clientId ?: null, 'from' => $this->from, 'to' => $this->to];
    }

    public function getRows(): array
    {
        return app(PerformanceService::class)->breakdown(static::$groupBy, $this->getFilters());
    }

    public function getSummary(): array
    {
        return app(PerformanceService::class)->summary($this->getFilters());
    }

    public function getClients(): array
    {
        return Client::orderBy('name')->pluck('name', 'id')->all();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('excel')->label('Export Excel')->icon('heroicon-o-table-cells')->color('gray')
                ->action(fn () => app(ReportExporter::class)->excel(static::$groupBy, $this->getFilters())),
            Action::make('pdf')->label('Export PDF')->icon('heroicon-o-document-arrow-down')
                ->action(fn () => app(ReportExporter::class)->pdf(static::$groupBy, $this->getFilters())),
        ];
    }
}
