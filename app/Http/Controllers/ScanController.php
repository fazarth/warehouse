<?php

namespace App\Http\Controllers;

use App\Models\GoodsIssue;
use App\Models\GoodsReceipt;
use App\Models\Location;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\QrCodeService;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    public function __construct(private QrCodeService $qrCodes)
    {
    }

    public function index(Request $request)
    {
        $code = strtoupper(trim((string) $request->get('code', '')));
        $result = null;
        $entity = null;
        $actions = [];

        if ($code !== '') {
            $result = $this->qrCodes->resolve($code);
            if ($result) {
                $entity = $this->qrCodes->resolveEntity($code);
                $actions = $this->actionsFor($result->entity_type, $entity);
            } else {
                // Fallback: try raw business codes without qr_codes row
                $entity = $this->fallbackResolve($code);
                if ($entity) {
                    $result = $this->qrCodes->ensureFor($entity, $code);
                    $actions = $this->actionsFor($result->entity_type, $entity);
                }
            }
        }

        return view('scan.index', compact('code', 'result', 'entity', 'actions'));
    }

    public function lookup(Request $request)
    {
        $request->validate(['code' => 'required|string|max:80']);

        return redirect()->route('scan.index', ['code' => strtoupper(trim($request->code))]);
    }

    protected function actionsFor(string $type, $entity): array
    {
        if (!$entity) {
            return [];
        }

        return match ($type) {
            'product' => [
                ['label' => 'Lihat Produk', 'url' => route('products.show', $entity), 'icon' => '📦'],
                ['label' => 'Kartu Stok', 'url' => route('inventory.stock-card', $entity), 'icon' => '🧾'],
                ['label' => 'Buat Penerimaan', 'url' => route('goods-receipts.create'), 'icon' => '📥'],
                ['label' => 'Buat Pengiriman', 'url' => route('goods-issues.create'), 'icon' => '📤'],
            ],
            'location' => [
                ['label' => 'Detail Lokasi', 'url' => route('locations.show', $entity), 'icon' => '📍'],
                ['label' => 'Cetak Label QR', 'url' => route('locations.label', $entity), 'icon' => '🖨️'],
                ['label' => 'Stok Gudang', 'url' => route('inventory.index', ['warehouse_id' => $entity->warehouse_id]), 'icon' => '📊'],
                ['label' => 'Mutasi Stok', 'url' => route('inventory.mutation'), 'icon' => '🔄'],
            ],
            'warehouse' => [
                ['label' => 'Lokasi di Gudang', 'url' => route('locations.index', ['warehouse_id' => $entity->id]), 'icon' => '🏭'],
                ['label' => 'Stok Gudang', 'url' => route('inventory.index', ['warehouse_id' => $entity->id]), 'icon' => '📊'],
            ],
            'supplier' => [
                ['label' => 'Edit Supplier', 'url' => route('suppliers.edit', $entity), 'icon' => '🚚'],
                ['label' => 'Buat Penerimaan', 'url' => route('goods-receipts.create'), 'icon' => '📥'],
            ],
            default => [],
        };
    }

    protected function fallbackResolve(string $code)
    {
        if (str_starts_with($code, 'LOC-')) {
            $raw = substr($code, 4);
            return Location::where('code', $raw)->orWhere('code', $code)->first();
        }
        if (str_starts_with($code, 'ITEM-')) {
            $raw = substr($code, 5);
            return Product::where('sku', $raw)
                ->orWhere('sku', str_replace('-', '-', $raw))
                ->orWhere('barcode', $code)
                ->orWhere('barcode', $raw)
                ->first()
                ?? Product::whereRaw('UPPER(REPLACE(sku, "_", "-")) = ?', [str_replace('_', '-', $raw)])->first();
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
        if (str_starts_with($code, 'GI-') || str_starts_with($code, 'DO-')) {
            return GoodsIssue::where('issue_number', $code)->first();
        }

        return Product::where('barcode', $code)->orWhere('sku', $code)->first()
            ?? Warehouse::where('code', $code)->first()
            ?? Supplier::where('code', $code)->first()
            ?? Location::where('code', $code)->first();
    }
}
