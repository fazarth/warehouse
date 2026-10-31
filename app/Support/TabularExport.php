<?php

namespace App\Support;

use App\Models\AuditLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TabularExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function __construct(
        protected array $headings,
        protected array $rows
    ) {
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function array(): array
    {
        return $this->rows;
    }

    /**
     * @param  Collection|array  $rows
     */
    public static function download(
        string $basename,
        array $headings,
        $rows,
        string $format = 'csv',
        ?string $title = null
    ): StreamedResponse|BinaryFileResponse|\Illuminate\Http\Response {
        $normalized = [];
        foreach ($rows as $row) {
            $normalized[] = array_values(is_array($row) ? $row : (array) $row);
        }

        $format = strtolower($format);
        if (! in_array($format, ['csv', 'xlsx', 'pdf'], true)) {
            $format = 'csv';
        }

        if ($format === 'csv') {
            return response()->streamDownload(function () use ($headings, $normalized) {
                $out = fopen('php://output', 'w');
                fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
                fputcsv($out, $headings);
                foreach ($normalized as $row) {
                    fputcsv($out, $row);
                }
                fclose($out);
            }, $basename . '.csv', [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        if ($format === 'xlsx') {
            return Excel::download(
                new self($headings, $normalized),
                $basename . '.xlsx'
            );
        }

        $pdf = Pdf::loadView('reports.pdf.table', [
            'title' => $title ?: $basename,
            'headings' => $headings,
            'rows' => $normalized,
            'generatedAt' => now()->format('d M Y H:i'),
            'user' => auth()->user()?->name,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($basename . '.pdf');
    }

    public static function logExport(string $report, string $format, int $rowCount = 0): void
    {
        AuditLog::log(
            'report.export',
            null,
            null,
            "Export laporan {$report} ({$format}) · {$rowCount} baris",
            null,
            ['report' => $report, 'format' => $format, 'rows' => $rowCount]
        );
    }
}
