@extends('layouts.app')

@section('title', 'Buat Return')
@section('page-title', 'Buat Stock Return')

@section('content')
<div x-data="{ items: [{}] }">
<form method="POST" action="{{ route('returns.store') }}" class="space-y-6">
    @csrf
    <div class="bg-white rounded-xl border p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="text-sm font-medium">Source *</label>
            <select name="source" required class="w-full mt-1 rounded-xl border-gray-200">
                @foreach(['customer','supplier','internal'] as $s)
                <option value="{{ $s }}" @selected(old('source','customer')===$s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-sm font-medium">Warehouse *</label>
            <select name="warehouse_id" required class="w-full mt-1 rounded-xl border-gray-200">
                @foreach($warehouses as $wh)
                <option value="{{ $wh->id }}" @selected(old('warehouse_id',$wh->code==='WH-MAIN'?$wh->id:null)==$wh->id)>{{ $wh->code }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-sm font-medium">Default Bin</label>
            <select name="location_id" class="w-full mt-1 rounded-xl border-gray-200">
                <option value="">— staging / per-item —</option>
                @foreach($locations as $loc)
                <option value="{{ $loc->id }}" @selected(old('location_id')==$loc->id)>{{ $loc->code }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-sm font-medium">Tanggal *</label>
            <input type="date" name="return_date" value="{{ old('return_date', date('Y-m-d')) }}" required class="w-full mt-1 rounded-xl border-gray-200">
        </div>
        <div>
            <label class="text-sm font-medium">Link GI (opsional)</label>
            <select name="goods_issue_id" class="w-full mt-1 rounded-xl border-gray-200">
                <option value="">—</option>
                @foreach($issues as $gi)
                <option value="{{ $gi->id }}" @selected(old('goods_issue_id',$prefillGi)==$gi->id)>{{ $gi->issue_number }} → {{ $gi->destination }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-sm font-medium">Reason</label>
            <input type="text" name="reason" value="{{ old('reason') }}" class="w-full mt-1 rounded-xl border-gray-200" placeholder="Damaged / wrong item / ...">
        </div>
        <div class="md:col-span-3">
            <label class="text-sm font-medium">Notes</label>
            <textarea name="notes" rows="2" class="w-full mt-1 rounded-xl border-gray-200">{{ old('notes') }}</textarea>
        </div>
    </div>

    <div class="bg-white rounded-xl border p-6">
        <div class="flex justify-between mb-3">
            <h3 class="font-bold">Items</h3>
            <button type="button" @click="items.push({})" class="text-sm px-3 py-1 bg-indigo-50 text-indigo-700 rounded-lg">+ Item</button>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-2 py-2 text-left">Produk</th>
                    <th class="px-2 py-2 text-left">Bin</th>
                    <th class="px-2 py-2 text-left">Batch</th>
                    <th class="px-2 py-2 text-left">Exp</th>
                    <th class="px-2 py-2 text-left">Qty</th>
                    <th class="px-2 py-2 text-left">Cond</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(item, idx) in items" :key="idx">
                    <tr>
                        <td class="px-2 py-2">
                            <select :name="'items['+idx+'][product_id]'" required class="w-full rounded-lg border-gray-200 text-sm">
                                <option value="">Pilih</option>
                                @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->sku }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-2 py-2">
                            <select :name="'items['+idx+'][location_id]'" class="w-full rounded-lg border-gray-200 text-sm">
                                <option value="">default</option>
                                @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->code }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-2 py-2"><input type="text" :name="'items['+idx+'][batch_number]'" class="w-24 rounded-lg border-gray-200 text-sm"></td>
                        <td class="px-2 py-2"><input type="date" :name="'items['+idx+'][expired_date]'" class="rounded-lg border-gray-200 text-sm"></td>
                        <td class="px-2 py-2"><input type="number" min="1" :name="'items['+idx+'][quantity]'" required class="w-20 rounded-lg border-gray-200 text-sm" value="1"></td>
                        <td class="px-2 py-2">
                            <select :name="'items['+idx+'][condition]'" class="rounded-lg border-gray-200 text-sm">
                                <option value="good">good</option>
                                <option value="damaged">damaged</option>
                                <option value="expired">expired</option>
                            </select>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <div class="flex gap-3">
        <button class="px-6 py-3 bg-indigo-600 text-white rounded-xl">Simpan Draft</button>
        <a href="{{ route('returns.index') }}" class="px-6 py-3 bg-gray-100 rounded-xl">Batal</a>
    </div>
</form>
</div>
@endsection
