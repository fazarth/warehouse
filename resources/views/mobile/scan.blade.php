<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="theme-color" content="#0f766e">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/icons/wms-192.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/wms-192.png">
    <title>Mobile Scan · WMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { -webkit-tap-highlight-color: transparent; }
        .scan-input { font-size: 18px; letter-spacing: 0.04em; }
    </style>
</head>
<body class="bg-slate-950 text-white min-h-screen">
<div class="max-w-lg mx-auto p-4 space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-bold">📷 Mobile Scanner</h1>
            <p class="text-xs text-teal-300">PWA · Zebra / wedge keyboard ready</p>
        </div>
        <div class="flex items-center gap-3">
            <button id="install-btn" type="button" class="hidden text-xs px-3 py-1.5 rounded-lg bg-teal-700 hover:bg-teal-600">Install</button>
            <a href="{{ route('dashboard') }}" class="text-xs text-slate-400 underline">Desktop</a>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-2 text-center text-xs">
        <div class="bg-slate-900 rounded-xl p-3 border border-slate-800">
            <div class="text-2xl font-bold text-amber-400">{{ $openPutaways }}</div>
            <div class="text-slate-400">Open putaway</div>
        </div>
        <div class="bg-slate-900 rounded-xl p-3 border border-slate-800">
            <div class="text-2xl font-bold text-sky-400">{{ $openPicks }}</div>
            <div class="text-slate-400">Open pick/pack</div>
        </div>
    </div>

    <form method="GET" action="{{ route('mobile.scan') }}" class="bg-slate-900 rounded-2xl border border-slate-800 p-4 space-y-3" id="scan-form">
        <label class="text-xs text-slate-400 uppercase tracking-wider">Scan / ketik kode</label>
        <input
            id="code"
            name="code"
            value="{{ $code }}"
            autofocus
            autocomplete="off"
            autocapitalize="characters"
            class="scan-input w-full rounded-xl bg-slate-950 border border-teal-700 px-4 py-3 text-teal-100 focus:outline-none focus:ring-2 focus:ring-teal-500"
            placeholder="LOC-A-01-01 / ITEM-SKU-..."
        >
        <button class="w-full py-3 bg-teal-600 hover:bg-teal-500 rounded-xl font-semibold">Lookup</button>
    </form>

    @if($code !== '')
        @if($entity)
        <div class="bg-slate-900 rounded-2xl border border-teal-800 p-4 space-y-2">
            <div class="text-xs text-teal-400 uppercase">{{ $result?->entity_type ?? class_basename($entity) }}</div>
            <div class="text-xl font-bold">{{ $entity->name ?? $entity->code ?? $entity->sku ?? '#' . $entity->id }}</div>
            <div class="text-sm text-slate-400 font-mono">{{ $code }}</div>
            @if($actions)
            <div class="flex flex-wrap gap-2 pt-2">
                @foreach($actions as $a)
                <a href="{{ $a['url'] }}" class="px-3 py-2 bg-slate-800 rounded-lg text-xs text-teal-200">{{ $a['label'] }}</a>
                @endforeach
            </div>
            @endif
        </div>

        @if($stocks->isNotEmpty())
        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-4">
            <h2 class="text-sm font-semibold mb-2 text-slate-300">Stock</h2>
            <ul class="space-y-2 text-sm">
                @foreach($stocks as $s)
                <li class="flex justify-between border-b border-slate-800 pb-2">
                    <span class="text-slate-300">
                        {{ $s->product?->sku ?? $s->warehouse?->code }}
                        <span class="text-xs text-slate-500">{{ $s->location?->code ?? $s->warehouse?->code }} · {{ $s->batch_number ?? '—' }}</span>
                    </span>
                    <span class="font-bold text-teal-300">{{ $s->quantity }}</span>
                </li>
                @endforeach
            </ul>
        </div>
        @endif
        @else
        <div class="bg-red-950 border border-red-800 rounded-2xl p-4 text-red-200 text-sm">
            Kode <span class="font-mono">{{ $code }}</span> tidak ditemukan.
        </div>
        @endif
    @endif

    <p class="text-[11px] text-slate-500 text-center pt-4">
        Tip: Chrome Android → menu → Install app. Scanner Zebra kirim Enter setelah barcode.
    </p>
</div>
<script>
const input = document.getElementById('code');
if (input) {
    input.focus();
    input.select();
    window.addEventListener('focus', () => input.focus());
}

if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js').catch(() => {});
}

let deferredPrompt = null;
const installBtn = document.getElementById('install-btn');
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    if (installBtn) installBtn.classList.remove('hidden');
});
if (installBtn) {
    installBtn.addEventListener('click', async () => {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        await deferredPrompt.userChoice;
        deferredPrompt = null;
        installBtn.classList.add('hidden');
    });
}
</script>
</body>
</html>
