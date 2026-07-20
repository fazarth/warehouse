@extends('layouts.app')

@section('title', 'Buat Cycle Count')
@section('page-title', 'Buat Cycle Count')

@section('content')
<form method="POST" action="{{ route('cycle-counts.store') }}" class="max-w-xl space-y-4 bg-white rounded-2xl border p-6">
    @csrf
    <div>
        <label class="text-sm font-medium">Warehouse *</label>
        <select name="warehouse_id" required class="w-full mt-1 rounded-xl border-gray-200">
            @foreach($warehouses as $wh)
            <option value="{{ $wh->id }}" @selected($wh->code==='WH-MAIN')>{{ $wh->code }} — {{ $wh->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-sm font-medium">Bin (opsional — kosong = semua bin)</label>
        <select name="location_id" class="w-full mt-1 rounded-xl border-gray-200">
            <option value="">Semua bin gudang</option>
            @foreach($locations as $loc)
            <option value="{{ $loc->id }}">{{ $loc->code }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-sm font-medium">Tanggal *</label>
        <input type="date" name="stockopname_date" value="{{ date('Y-m-d') }}" required class="w-full mt-1 rounded-xl border-gray-200">
    </div>
    <div>
        <label class="text-sm font-medium">Notes</label>
        <textarea name="notes" rows="2" class="w-full mt-1 rounded-xl border-gray-200"></textarea>
    </div>
    <div class="flex gap-3">
        <button class="px-6 py-3 bg-teal-600 text-white rounded-xl">Generate lines</button>
        <a href="{{ route('cycle-counts.index') }}" class="px-6 py-3 bg-gray-100 rounded-xl">Batal</a>
    </div>
</form>
@endsection
