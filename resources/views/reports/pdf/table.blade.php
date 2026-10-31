<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0f172a; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        .meta { color: #64748b; margin-bottom: 12px; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 5px 6px; text-align: left; vertical-align: top; }
        th { background: #eef2ff; font-weight: bold; }
        tr:nth-child(even) td { background: #f8fafc; }
        .footer { margin-top: 10px; color: #94a3b8; font-size: 9px; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="meta">
        Digenerate: {{ $generatedAt }}
        @if(!empty($user)) · oleh {{ $user }} @endif
        · {{ count($rows) }} baris
    </div>
    <table>
        <thead>
            <tr>
                @foreach($headings as $h)
                    <th>{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{{ is_scalar($cell) || $cell === null ? $cell : json_encode($cell) }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ max(1, count($headings)) }}">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">Enterprise WMS · confidential internal use</div>
</body>
</html>
