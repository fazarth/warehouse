@extends('layouts.app')

@section('title', 'Buat PO')
@section('page-title', 'Buat Purchase Order')

@section('content')
<div x-data="poForm()">
    <form method="POST" action="{{ route('purchase-orders.store') }}" class="space-y-6">
        @csrf
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">Supplier *</label>
                <select name="supplier_id" required class="w-full rounded-xl border-gray-200">
                    <option value="">Pilih</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" @selected(old('supplier_id')==$s->id)>{{ $s->code }} — {{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Tanggal PO *</label>
                <input type="date" name="order_date" value="{{ old('order_date', date('Y-m-d')) }}" required class="w-full rounded-xl border-gray-200">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">ETA</label>
                <input type="date" name="expected_date" value="{{ old('expected_date') }}" class="w-full rounded-xl border-gray-200">
            </div>
            <div class="md:col-span-3">
                <label class="block text-sm font-medium mb-2">Catatan</label>
                <textarea name="notes" rows="2" class="w-full rounded-xl border-gray-200">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <div class="flex justify-between mb-4">
                <h3 class="font-bold">Items</h3>
                <button type="button" @click="addItem()" class="px-3 py-2 bg-indigo-50 text-indigo-700 rounded-xl text-sm">+ Item</button>
            </div>
            <table class="w-full">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-3 py-2 text-left">Produk</th>
                        <th class="px-3 py-2 text-left">Qty</th>
                        <th class="px-3 py-2 text-left">Harga</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(item, idx) in items" :key="idx">
                        <tr>
                            <td class="px-3 py-2">
                                <select :name="'items['+idx+'][product_id]'" required class="w-full rounded-lg border-gray-200 text-sm">
                                    <option value="">Pilih</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->sku }} — {{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" min="1" :name="'items['+idx+'][quantity_ordered]'" required class="w-28 rounded-lg border-gray-200 text-sm">
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" min="0" step="0.01" :name="'items['+idx+'][unit_price]'" class="w-32 rounded-lg border-gray-200 text-sm">
                            </td>
                            <td class="px-3 py-2">
                                <button type="button" @click="items.splice(idx,1)" class="text-red-500">✕</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('purchase-orders.index') }}" class="px-4 py-2 rounded-xl bg-gray-100">Batal</a>
            <button class="px-4 py-2 rounded-xl bg-indigo-600 text-white">Simpan PO</button>
        </div>
    </form>
</div>

<script>
function poForm() {
    return {
        items: [{ product_id: '', quantity_ordered: 1 }],
        addItem() { this.items.push({ product_id: '', quantity_ordered: 1 }); }
    }
}
</script>
@endsection
