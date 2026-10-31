<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="theme-color" content="#4f46e5">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/icons/wms-192.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/wms-192.png">
    <title>Mobile Scan · WMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: Inter, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            -webkit-tap-highlight-color: transparent;
            background: #f8fafc;
            color: #0f172a;
        }
        .scan-input { font-size: 18px; letter-spacing: 0.03em; }
    </style>
</head>
<body class="min-h-screen">
<div class="max-w-lg mx-auto p-4 space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-semibold text-slate-900">Mobile Scanner</h1>
            <p class="text-xs text-slate-500">PWA · Zebra / wedge keyboard ready</p>
        </div>
        <div class="flex items-center gap-2">
            <button id="install-btn" type="button" class="hidden text-xs px-3 py-1.5 rounded-lg text-white font-medium" style="background:#4f46e5;">Install</button>
            <a href="{{ route('dashboard') }}" class="text-xs font-medium text-indigo-600">Desktop</a>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 text-center text-xs">
        <div class="bg-white rounded-xl p-3 border border-slate-200">
            <div class="text-2xl font-semibold text-slate-900">{{ $openPutaways }}</div>
            <div class="text-slate-500 mt-0.5">Open putaway</div>
        </div>
        <div class="bg-white rounded-xl p-3 border border-slate-200">
            <div class="text-2xl font-semibold text-slate-900">{{ $openPicks }}</div>
            <div class="text-slate-500 mt-0.5">Open pick/pack</div>
        </div>
    </div>

    <form method="GET" action="{{ route('mobile.scan') }}" class="bg-white rounded-xl border border-slate-200 p-4 space-y-3" id="scan-form">
        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Scan / ketik kode</label>
        <input
            id="code"
            name="code"
            value="{{ $code }}"
            autofocus
            autocomplete="off"
            autocapitalize="characters"
            class="scan-input w-full rounded-lg bg-white border border-slate-300 px-4 py-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400"
            placeholder="LOC-A-01-01 / ITEM-SKU-..."
        >
        <button class="w-full py-3 rounded-lg font-semibold text-white" style="background:#4f46e5;">Lookup</button>
    </form>

    @if($code !== '')
        @if($entity)
        <div class="bg-white rounded-xl border border-slate-200 p-4 space-y-2">
            <div class="text-xs font-semibold uppercase tracking-wide text-indigo-600">{{ $result?->entity_type ?? class_basename($entity) }}</div>
            <div class="text-xl font-semibold text-slate-900">{{ $entity->name ?? $entity->code ?? $entity->sku ?? '#' . $entity->id }}</div>
            <div class="text-sm text-slate-500 font-mono">{{ $code }}</div>
            @if($actions)
            <div class="flex flex-wrap gap-2 pt-2">
                @foreach($actions as $a)
                <a href="{{ $a['url'] }}" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium text-slate-700">{{ $a['label'] }}</a>
                @endforeach
            </div>
            @endif
        </div>

        @if($stocks->isNotEmpty())
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <h2 class="text-sm font-semibold mb-2 text-slate-800">Stock</h2>
            <ul class="space-y-2 text-sm">
                @foreach($stocks as $s)
                <li class="flex justify-between border-b border-slate-100 pb-2">
                    <span class="text-slate-700">
                        {{ $s->product?->sku ?? $s->warehouse?->code }}
                        <span class="text-xs text-slate-400">{{ $s->location?->code ?? $s->warehouse?->code }} · {{ $s->batch_number ?? '—' }}</span>
                    </span>
                    <span class="font-semibold text-slate-900">{{ $s->quantity }}</span>
                </li>
                @endforeach
            </ul>
        </div>
        @endif
        @else
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-700 text-sm">
            Kode <span class="font-mono">{{ $code }}</span> tidak ditemukan.
        </div>
        @endif
    @endif

    <p class="text-[11px] text-slate-400 text-center pt-2">
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
