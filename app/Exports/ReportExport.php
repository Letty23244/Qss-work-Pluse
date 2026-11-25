<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function __construct(protected array $rows, protected array $summary) {}

    public function headings(): array
    {
        return ['Group', 'Target', 'Produced', 'Pending', 'Remaining', 'Achievement %'];
    }

    public function array(): array
    {
        $map = fn ($label, $r) => [$label, $r['target'], $r['produced'], $r['pending'], $r['remaining'], $r['achievement'] ?? '—'];

        return array_merge(
            array_map(fn ($r) => $map($r['label'], $r), $this->rows),
            [$map('TOTAL', $this->summary)]
        );
    }
}
