@extends('layouts.app')

@section('title', 'Buat Penerimaan Barang')
@section('page-title', 'Buat Penerimaan Barang')

@section('content')
<div x-data="goodsReceiptForm()">
    <form action="{{ route('goods-receipts.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <!-- Header Info -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Informasi Penerimaan</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Purchase Order</label>
                                <select name="purchase_order_id" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition"
                                        onchange="if(this.value){ window.location='{{ route('goods-receipts.create') }}?purchase_order_id='+this.value }">
                                    <option value="">Tanpa PO (ad-hoc)</option>
                                    @foreach($purchaseOrders as $po)
                                    <option value="{{ $po->id }}" @selected(old('purchase_order_id', $selectedPo?->id)==$po->id)>
                                        {{ $po->po_number }} — {{ $po->supplier?->name }} ({{ $po->status }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Supplier *</label>
                                <select name="supplier_id" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition">
                                    <option value="">Pilih Supplier</option>
                                    @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" @selected(old('supplier_id', $selectedPo?->supplier_id)==$supplier->id)>{{ $supplier->code }} - {{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                                @error('supplier_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Terima *</label>
                                <input type="date" name="receipt_date" value="{{ old('receipt_date', date('Y-m-d')) }}" required
                                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition">
                            </div>
                
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">No. Surat Jalan</label>
                                <input type="text" name="document_number" value="{{ old('document_number') }}"
                                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition"
                                       placeholder="Opsional">
                            </div>
                        </div>
            
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                <textarea name="notes" rows="2" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition">{{ old('notes') }}</textarea>
            </div>
        </div>
        
        <!-- Items -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">Daftar Item</h3>
                <button type="button" @click="addItem()" class="px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl transition text-sm font-medium">
                    + Tambah Item
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                            <th class="px-4 py-3">Produk *</th>
                            <th class="px-4 py-3">Lokasi *</th>
                            <th class="px-4 py-3">Qty *</th>
                            <th class="px-4 py-3">Batch</th>
                            <th class="px-4 py-3">Lot</th>
                            <th class="px-4 py-3">Expired</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(item, idx) in items" :key="idx">
                            <tr>
                                <td class="px-4 py-3">
                                                                    <input type="hidden" :name="'items['+idx+'][purchase_order_item_id]'" :value="item.purchase_order_item_id || ''">
                                                                    <select :name="'items['+idx+'][product_id]'" x-model="item.product_id" required class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-indigo-400 text-sm">
                                                                        <option value="">Pilih</option>
                                                                        @foreach($products as $product)
                                                                        <option value="{{ $product->id }}">{{ $product->sku }} - {{ $product->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td class="px-4 py-3">
                                                                    <select :name="'items['+idx+'][warehouse_id]'" required class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-indigo-400 text-sm">
                                                                        <option value="">Pilih</option>
                                                                        @foreach($warehouses as $wh)
                                                                        <option value="{{ $wh->id }}" @selected($wh->code==='WH-MAIN')>{{ $wh->code }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td class="px-4 py-3">
                                                                    <input type="number" :name="'items['+idx+'][quantity]'" x-model="item.quantity" step="1" min="1" required
                                                                           class="w-24 px-3 py-2 rounded-lg border border-gray-200 focus:border-indigo-400 text-sm">
                                                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" :name="'items['+idx+'][batch_number]'" 
                                           class="w-24 px-3 py-2 rounded-lg border border-gray-200 focus:border-indigo-400 text-sm" placeholder="-">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" :name="'items['+idx+'][lot_number]'" 
                                           class="w-24 px-3 py-2 rounded-lg border border-gray-200 focus:border-indigo-400 text-sm" placeholder="-">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="date" :name="'items['+idx+'][expired_date]'" 
                                           class="w-36 px-3 py-2 rounded-lg border border-gray-200 focus:border-indigo-400 text-sm">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button" @click="removeItem(idx)" x-show="items.length > 1" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition">Hapus</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl transition">
                Simpan Penerimaan
            </button>
            <a href="{{ route('goods-receipts.index') }}" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition">
                Batal
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function goodsReceiptForm() {
    @php
        $seed = [];
        if ($selectedPo) {
            foreach ($selectedPo->items as $poi) {
                $remain = max(0, (int)$poi->quantity_ordered - (int)$poi->quantity_received);
                if ($remain <= 0) continue;
                $seed[] = [
                    'product_id' => (string) $poi->product_id,
                    'quantity' => $remain,
                    'purchase_order_item_id' => $poi->id,
                ];
            }
        }
        if (!$seed) $seed = [['product_id' => '', 'quantity' => '']];
    @endphp
    return {
        items: @json($seed),
        addItem() {
            this.items.push({ product_id: '', quantity: '', purchase_order_item_id: null });
        },
        removeItem(idx) {
            this.items.splice(idx, 1);
        }
    }
}
</script>
@endpush
@endsection
