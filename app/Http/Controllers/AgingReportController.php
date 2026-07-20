<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Services\AgingService;
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

        $summary = $this->aging->summary($warehouseId);
        $warehouses = Warehouse::orderBy('name')->get();

        return view('reports.aging', compact('rows', 'summary', 'warehouses', 'warehouseId', 'bucket'));
    }
}
