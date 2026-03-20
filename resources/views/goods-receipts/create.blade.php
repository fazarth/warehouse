@extends('layouts.app')

@section('title', 'Buat Penerimaan Barang')
@section('page-title', 'Buat Penerimaan Barang')

@section('content')
<div x-data="goodsReceiptForm()">
    <form action="{{ route('goods-receipts.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <!-- Header Info -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Informasi Penerimaan</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Supplier *</label>
                    <select name="supplier_id" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition">
                        <option value="">Pilih Supplier</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->code }} - {{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Terima *</label>
                    <input type="date" name="receipt_date" value="{{ old('receipt_date', date('Y-m-d')) }}" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">No. Surat Jalan</label>
                    <input type="text" name="delivery_note" value="{{ old('delivery_note') }}"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition"
                           placeholder="Opsional">
                </div>
            </div>
            
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                <textarea name="notes" rows="2" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition">{{ old('notes') }}</textarea>
            </div>
        </div>
        
        <!-- Items -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">Daftar Item</h3>
                <button type="button" @click="addItem()" class="px-4 py-2 bg-teal-100 hover:bg-teal-200 text-teal-700 rounded-xl transition text-sm font-medium">
                    ➕ Tambah Item
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
                                    <select :name="'items['+idx+'][product_id]'" required class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-teal-500 text-sm">
                                        <option value="">Pilih</option>
                                        @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->sku }} - {{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <select :name="'items['+idx+'][warehouse_id]'" required class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-teal-500 text-sm">
                                        <option value="">Pilih</option>
                                        @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}">{{ $wh->code }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" :name="'items['+idx+'][quantity]'" step="0.01" min="0.01" required
                                           class="w-24 px-3 py-2 rounded-lg border border-gray-200 focus:border-teal-500 text-sm">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" :name="'items['+idx+'][batch_number]'" 
                                           class="w-24 px-3 py-2 rounded-lg border border-gray-200 focus:border-teal-500 text-sm" placeholder="-">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" :name="'items['+idx+'][lot_number]'" 
                                           class="w-24 px-3 py-2 rounded-lg border border-gray-200 focus:border-teal-500 text-sm" placeholder="-">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="date" :name="'items['+idx+'][expired_date]'" 
                                           class="w-36 px-3 py-2 rounded-lg border border-gray-200 focus:border-teal-500 text-sm">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button" @click="removeItem(idx)" x-show="items.length > 1" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition">🗑️</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <button type="submit" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-xl transition">
                💾 Simpan Penerimaan
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
    return {
        items: [{}],
        addItem() {
            this.items.push({});
        },
        removeItem(idx) {
            this.items.splice(idx, 1);
        }
    }
}
</script>
@endpush
@endsection
