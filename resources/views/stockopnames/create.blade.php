@extends('layouts.app')

@section('title', 'Buat Stock Opname')
@section('page-title', 'Buat Stock Opname')

@section('content')
<div x-data="stockopnameForm()">
    <form action="{{ route('stockopnames.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Informasi Stock Opname</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Gudang *</label>
                    <select name="warehouse_id" x-model="warehouseId" @change="loadProducts()" required 
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition">
                        <option value="">Pilih Gudang</option>
                        @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->code }} - {{ $wh->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Opname *</label>
                    <input type="date" name="stockopname_date" value="{{ old('stockopname_date', date('Y-m-d')) }}" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition">
                </div>
            </div>
            
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                <textarea name="notes" rows="2" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition">{{ old('notes') }}</textarea>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">Daftar Item Opname</h3>
                <button type="button" @click="addItem()" class="px-4 py-2 bg-purple-100 hover:bg-purple-200 text-purple-700 rounded-xl transition text-sm font-medium">
                    ➕ Tambah Item
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                            <th class="px-4 py-3">Produk *</th>
                            <th class="px-4 py-3">Stok Sistem</th>
                            <th class="px-4 py-3">Stok Fisik *</th>
                            <th class="px-4 py-3">Selisih</th>
                            <th class="px-4 py-3">Catatan</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(item, idx) in items" :key="idx">
                            <tr>
                                <td class="px-4 py-3">
                                    <select :name="'items['+idx+'][product_id]'" required x-model="item.product_id" @change="updateSystemQty(idx)"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-teal-500 text-sm">
                                        <option value="">Pilih</option>
                                        @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->sku }} - {{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" :name="'items['+idx+'][system_qty]'" x-model="item.system_qty" readonly
                                           class="w-24 px-3 py-2 rounded-lg border border-gray-200 bg-gray-50 text-sm">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" :name="'items['+idx+'][physical_qty]'" step="0.01" min="0" required 
                                           x-model="item.physical_qty" @input="updateDiff(idx)"
                                           class="w-24 px-3 py-2 rounded-lg border border-gray-200 focus:border-teal-500 text-sm">
                                </td>
                                <td class="px-4 py-3">
                                    <span :class="item.diff > 0 ? 'text-green-600' : (item.diff < 0 ? 'text-red-600' : 'text-gray-600')" 
                                          class="font-semibold" x-text="item.diff ? (item.diff > 0 ? '+' : '') + item.diff : '0'"></span>
                                    <input type="hidden" :name="'items['+idx+'][difference]'" x-model="item.diff">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" :name="'items['+idx+'][notes]'" x-model="item.notes"
                                           class="w-32 px-3 py-2 rounded-lg border border-gray-200 focus:border-teal-500 text-sm" placeholder="-">
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
            <button type="submit" class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-xl transition">
                💾 Simpan Draft
            </button>
            <a href="{{ route('stockopnames.index') }}" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition">Batal</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function stockopnameForm() {
    return {
        warehouseId: '',
        items: [{product_id: '', system_qty: 0, physical_qty: 0, diff: 0, notes: ''}],
        addItem() { 
            this.items.push({product_id: '', system_qty: 0, physical_qty: 0, diff: 0, notes: ''}); 
        },
        removeItem(idx) { 
            this.items.splice(idx, 1); 
        },
        updateSystemQty(idx) {
            // In real implementation, fetch from API based on warehouse and product
            this.items[idx].system_qty = 0;
            this.updateDiff(idx);
        },
        updateDiff(idx) {
            let item = this.items[idx];
            item.diff = parseFloat(item.physical_qty || 0) - parseFloat(item.system_qty || 0);
        },
        loadProducts() {
            // Could be used to filter products by warehouse stock
        }
    }
}
</script>
@endpush
@endsection
