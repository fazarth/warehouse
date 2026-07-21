<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Warehouse;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    public function __construct(private QrCodeService $qrCodes)
    {
    }

    public function index(Request $request)
    {
        $warehouses = Warehouse::orderBy('name')->get();

        // Prefer explicit filter → WH-MAIN → first warehouse that already has locations → first warehouse
        $warehouseId = $request->integer('warehouse_id') ?: null;
        if (!$warehouseId) {
            $warehouseId = Warehouse::where('code', 'WH-MAIN')->value('id')
                ?: Location::query()->select('warehouse_id')->groupBy('warehouse_id')->value('warehouse_id')
                ?: $warehouses->first()?->id;
        }

        $locations = Location::with(['warehouse', 'parent', 'qrCode'])
            ->withCount('children')
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->search;
                $q->where(function ($qq) use ($s) {
                    $qq->where('code', 'like', "%{$s}%")
                        ->orWhere('name', 'like', "%{$s}%");
                });
            })
            ->orderByRaw("FIELD(type, 'zone', 'rack', 'bin')")
            ->orderBy('code')
            ->paginate(20)
            ->withQueryString();

        return view('locations.index', compact('locations', 'warehouses', 'warehouseId'));
    }

    public function create(Request $request)
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $parents = Location::with('warehouse')
            ->whereIn('type', [Location::TYPE_ZONE, Location::TYPE_RACK])
            ->orderBy('code')
            ->get();

        return view('locations.create', [
            'warehouses' => $warehouses,
            'parents' => $parents,
            'selectedWarehouse' => $request->integer('warehouse_id'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'parent_id' => 'nullable|exists:locations,id',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('locations')->where(fn ($q) => $q->where('warehouse_id', $request->warehouse_id)),
            ],
            'barcode' => 'nullable|string|max:80|unique:locations,barcode',
            'name' => 'required|string|max:255',
            'display_name' => 'nullable|string|max:120',
            'type' => 'required|in:zone,rack,bin',
            'capacity' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['code'] = strtoupper($validated['code']);
        $validated['barcode'] = $this->normalizeBarcode($validated['barcode'] ?? null, $validated['code']);
        $validated['display_name'] = $validated['display_name'] ?? $validated['name'];

        if (!empty($validated['parent_id'])) {
            $parent = Location::findOrFail($validated['parent_id']);
            if ((int) $parent->warehouse_id !== (int) $validated['warehouse_id']) {
                return back()->withInput()->with('error', 'Parent location harus di gudang yang sama.');
            }
        }

        $location = Location::create($validated);
        $this->qrCodes->ensureFor($location);

        return redirect()->route('locations.index', ['warehouse_id' => $location->warehouse_id])
            ->with('success', 'Lokasi ' . $location->code . ' berhasil ditambahkan.');
    }

    public function show(Location $location)
    {
        $location->load(['warehouse', 'parent', 'children.qrCode', 'qrCode', 'productStocks.product']);
        $qr = $location->qrCode ?: $this->qrCodes->ensureFor($location);

        return view('locations.show', compact('location', 'qr'));
    }

    public function edit(Location $location)
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $parents = Location::where('warehouse_id', $location->warehouse_id)
            ->where('id', '!=', $location->id)
            ->whereIn('type', [Location::TYPE_ZONE, Location::TYPE_RACK])
            ->orderBy('code')
            ->get();

        return view('locations.edit', compact('location', 'warehouses', 'parents'));
    }

    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'parent_id' => 'nullable|exists:locations,id',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('locations')
                    ->where(fn ($q) => $q->where('warehouse_id', $request->warehouse_id))
                    ->ignore($location->id),
            ],
            'barcode' => [
                'nullable',
                'string',
                'max:80',
                Rule::unique('locations', 'barcode')->ignore($location->id),
            ],
            'name' => 'required|string|max:255',
            'display_name' => 'nullable|string|max:120',
            'type' => 'required|in:zone,rack,bin',
            'capacity' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['code'] = strtoupper($validated['code']);
        $validated['barcode'] = $this->normalizeBarcode($validated['barcode'] ?? null, $validated['code']);
        $validated['display_name'] = $validated['display_name'] ?? $validated['name'];

        if (!empty($validated['parent_id']) && (int) $validated['parent_id'] === (int) $location->id) {
            return back()->withInput()->with('error', 'Lokasi tidak bisa menjadi parent dirinya sendiri.');
        }

        $location->update($validated);
        $this->qrCodes->ensureFor($location);

        return redirect()->route('locations.show', $location)
            ->with('success', 'Lokasi berhasil diperbarui.');
    }

    public function destroy(Location $location)
    {
        if ($location->children()->exists()) {
            return back()->with('error', 'Hapus child location terlebih dahulu.');
        }
        if ($location->productStocks()->where('quantity', '>', 0)->exists()) {
            return back()->with('error', 'Tidak bisa hapus lokasi yang masih berisi stok.');
        }

        $location->qrCode()?->delete();
        $location->delete();

        return redirect()->route('locations.index')
            ->with('success', 'Lokasi dihapus.');
    }

    public function label(Location $location)
    {
        $qr = $location->qrCode ?: $this->qrCodes->ensureFor($location);

        return view('locations.label', compact('location', 'qr'));
    }

    /** Normalize physical barcode; default LOC-{CODE} (SIPIL-inspired, not a copy). */
    protected function normalizeBarcode(?string $barcode, string $code): string
    {
        $code = strtoupper(trim($code));
        $barcode = strtoupper(trim((string) $barcode));
        if ($barcode === '') {
            return str_starts_with($code, 'LOC-') ? $code : 'LOC-'.$code;
        }

        return $barcode;
    }
}
