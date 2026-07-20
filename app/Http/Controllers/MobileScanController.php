<?php

namespace App\Http\Controllers;

use App\Models\GoodsIssue;
use App\Models\GoodsReceipt;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PutawayTask;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\QrCodeService;
use Illuminate\Http\Request;

/**
 * W5 mobile scanner — Zebra-friendly (hardware keyboard wedge → autofocus input).
 */
class MobileScanController extends Controller
{
    public function __construct(private QrCodeService $qrCodes)
    {
    }

    public function index(Request $request)
    {
        $code = strtoupper(trim((string) $request->get('code', '')));
        $result = null;
        $entity = null;
        $stocks = collect();
        $actions = [];

        if ($code !== '') {
            $result = $this->qrCodes->resolve($code);
            $entity = $result ? $this->qrCodes->resolveEntity($code) : $this->fallback($code);
            if ($entity && ! $result) {
                $result = $this->qrCodes->ensureFor($entity, $code);
            }
            if ($entity) {
                $actions = $this->actionsFor($result?->entity_type ?? class_basename($entity), $entity);
                if ($entity instanceof Product) {
                    $stocks = ProductStock::with(['warehouse', 'location'])
                        ->where('product_id', $entity->id)
                        ->where('quantity', '>', 0)
                        ->get();
                }
                if ($entity instanceof Location) {
                    $stocks = ProductStock::with(['product', 'warehouse'])
                        ->where('location_id', $entity->id)
                        ->where('quantity', '>', 0)
                        ->get();
                }
            }
        }

        $openPutaways = PutawayTask::whereIn('status', ['pending', 'in_progress'])->count();
        $openPicks = GoodsIssue::whereIn('status', ['picking', 'packing', 'packed'])->count();

        return view('mobile.scan', compact('code', 'result', 'entity', 'stocks', 'actions', 'openPutaways', 'openPicks'));
    }

    public function lookup(Request $request)
    {
        $request->validate(['code' => 'required|string|max:80']);

        return redirect()->route('mobile.scan', ['code' => strtoupper(trim($request->code))]);
    }

    protected function actionsFor(string $type, $entity): array
    {
        $type = strtolower($type);
        $user = auth()->user();

        // Prefer floor routes Operator can open; never push master pages without permission.
        return match (true) {
            $type === 'product' || $entity instanceof Product => array_values(array_filter([
                $user?->can('inventory.view')
                    ? ['label' => 'Kartu Stok', 'url' => route('inventory.stock-card', $entity)]
                    : null,
                $user?->can('putaway.view')
                    ? ['label' => 'Put Away', 'url' => route('putaways.index')]
                    : null,
            ])),
            $type === 'location' || $entity instanceof Location => array_values(array_filter([
                $user?->can('location.view')
                    ? ['label' => 'Detail Bin', 'url' => route('locations.show', $entity)]
                    : null,
                $user?->can('location.view')
                    ? ['label' => 'Label QR', 'url' => route('locations.label', $entity)]
                    : null,
                $user?->can('putaway.view')
                    ? ['label' => 'Put Away', 'url' => route('putaways.index')]
                    : null,
                $user?->can('inventory.view')
                    ? ['label' => 'Stok Gudang', 'url' => route('inventory.index')]
                    : null,
            ])),
            $type === 'warehouse' || $entity instanceof Warehouse => array_values(array_filter([
                $user?->can('inventory.view')
                    ? ['label' => 'Stok', 'url' => route('inventory.index', ['warehouse_id' => $entity->id])]
                    : null,
            ])),
            default => [],
        };
    }

    protected function fallback(string $code)
    {
        if (str_starts_with($code, 'LOC-')) {
            $raw = substr($code, 4);

            return Location::where('code', $raw)->orWhere('code', $code)->first();
        }
        if (str_starts_with($code, 'ITEM-')) {
            $raw = substr($code, 5);

            return Product::where('sku', $raw)->orWhere('barcode', $code)->first();
        }
        if (str_starts_with($code, 'WH') || str_starts_with($code, 'WH-')) {
            return Warehouse::where('code', $code)->orWhere('code', str_replace('WH-', '', $code))->first();
        }
        if (str_starts_with($code, 'SUP-')) {
            return Supplier::where('code', $code)->first();
        }
        if (str_starts_with($code, 'GR-')) {
            return GoodsReceipt::where('receipt_number', $code)->first();
        }

        return Product::where('barcode', $code)->orWhere('sku', $code)->first()
            ?? Location::where('code', $code)->first()
            ?? Warehouse::where('code', $code)->first();
    }
}
