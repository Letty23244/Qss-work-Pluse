<?php

namespace App\Services;

use App\Exports\ReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ReportExporter
{
    public function __construct(protected PerformanceService $svc) {}

    public function data(string $by, array $filters): array
    {
        return [
            'title'   => 'QSS Performance Report ('.str_replace('_id', '', $by).')',
            'filters' => $filters,
            'summary' => $this->svc->summary($filters),
            'rows'    => $this->svc->breakdown($by, $filters),
        ];
    }

    public function excel(string $by, array $filters)
    {
        $d = $this->data($by, $filters);
        return Excel::download(new ReportExport($d['rows'], $d['summary']), 'qss-report-'.$by.'.xlsx');
    }

    public function pdf(string $by, array $filters)
    {
        return Pdf::loadView('reports.pdf', $this->data($by, $filters))->download('qss-report-'.$by.'.pdf');
    }
}
