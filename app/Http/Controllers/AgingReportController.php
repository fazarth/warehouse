<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Services\AgingService;
use App\Support\TabularExport;
use Illuminate\Http\Request;

class AgingReportController extends Controller
{
    public function __construct(private AgingService $aging)
    {
    }

    public function index(Request $request)
    {
        $warehouseId = $request->filled('warehouse_id') ? (int) $request->warehouse_id : null;
        $bucket = $request->get('bucket');

        $rows = $this->aging->report($warehouseId);
        if ($bucket) {
            $rows = $rows->where('bucket', $bucket)->values();
        }

        if ($format = $this->exportFormat($request)) {
            $mapped = $rows->map(fn ($r) => [
                $r['sku'] ?? '-',
                $r['warehouse'] ?? '-',
                $r['location'] ?? '-',
                $r['batch'] ?? '-',
                $r['expired_date'] ?? '-',
                $r['days_to_expiry'] ?? '-',
                $r['bucket'] ?? '-',
                $r['qty'] ?? 0,
            ]);

            TabularExport::logExport('report-aging', $format, $mapped->count());

            return TabularExport::download(
                'report-aging',
                ['SKU', 'Warehouse', 'Bin', 'Batch', 'Expired', 'Days', 'Bucket', 'Qty'],
                $mapped,
                $format,
                'Aging / Expiry Report'
            );
        }

        $summary = $this->aging->summary($warehouseId);
        $warehouses = Warehouse::orderBy('name')->get();

        return view('reports.aging', compact('rows', 'summary', 'warehouses', 'warehouseId', 'bucket'));
    }

    protected function exportFormat(Request $request): ?string
    {
        if ($request->filled('format')) {
            $format = strtolower((string) $request->input('format'));

            return in_array($format, ['csv', 'xlsx', 'pdf'], true) ? $format : null;
        }

        if ($request->boolean('export')) {
            return 'csv';
        }

        return null;
    }
}
