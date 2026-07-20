<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Label {{ $qr->code }}</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, sans-serif; margin: 24px; color: #111; }
        .label { width: 320px; border: 2px solid #0f766e; border-radius: 16px; padding: 20px; text-align: center; }
        .code { font-family: ui-monospace, monospace; font-size: 18px; font-weight: 700; color: #0f766e; margin: 8px 0; }
        .meta { font-size: 12px; color: #555; }
        @media print { body { margin: 0; } .no-print { display: none; } }
    </style>
</head>
<body>
    <p class="no-print"><button onclick="window.print()">Print</button> · <a href="{{ route('locations.show', $location) }}">Kembali</a></p>
    <div class="label">
        <div class="meta">{{ $location->warehouse?->name }} · {{ \App\Models\Location::typeLabel($location->type) }}</div>
        <div class="code">{{ $qr->code }}</div>
        <img src="{{ $qr->imageUrl(180) }}" width="180" height="180" alt="QR">
        <div style="margin-top:10px;font-weight:600">{{ $location->name }}</div>
        <div class="meta">{{ $location->code }} · {{ $location->fullPath() }}</div>
    </div>
</body>
</html>
