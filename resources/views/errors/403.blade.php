<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses ditolak — Warehouse Management</title>
    <style>
        :root {
            --page: #f8fafc;
            --surface: #ffffff;
            --ink: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
            --primary: #4f46e5;
            --primary-soft: #eef2ff;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            font-family: Inter, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            font-feature-settings: "cv01", "ss03";
            background: var(--page);
            color: var(--ink);
        }
        .card {
            width: 100%;
            max-width: 28rem;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 0.75rem;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            overflow: hidden;
        }
        .head {
            padding: 1.5rem 1.5rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            text-align: center;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 3rem;
            height: 2rem;
            padding: 0 0.75rem;
            border-radius: 0.5rem;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            font-size: 0.8125rem;
            font-weight: 700;
            letter-spacing: 0.04em;
        }
        h1 {
            margin: 0.85rem 0 0.35rem;
            font-size: 1.125rem;
            font-weight: 600;
            letter-spacing: -0.01em;
        }
        .body {
            padding: 1.25rem 1.5rem 1.5rem;
            text-align: center;
        }
        p {
            margin: 0;
            font-size: 0.875rem;
            line-height: 1.55;
            color: var(--muted);
        }
        .hint {
            margin-top: 0.75rem;
            padding: 0.75rem 0.875rem;
            border-radius: 0.5rem;
            background: var(--primary-soft);
            border: 1px solid #c7d2fe;
            color: #3730a3;
            font-size: 0.8125rem;
            text-align: left;
        }
        .actions {
            margin-top: 1.25rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
        }
        a.btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.55rem 0.95rem;
            border-radius: 0.5rem;
            font-size: 0.8125rem;
            font-weight: 600;
            text-decoration: none;
        }
        a.btn-primary {
            background: var(--primary);
            color: #fff;
        }
        a.btn-secondary {
            background: #fff;
            color: #334155;
            border: 1px solid var(--line);
        }
        .foot {
            margin-top: 1rem;
            font-size: 0.6875rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="head">
            <div class="badge">403</div>
            <h1>Akses ditolak</h1>
        </div>
        <div class="body">
            <p>
                @if(!empty($exception) && method_exists($exception, 'getMessage') && filled($exception->getMessage()) && !str_contains(strtolower($exception->getMessage()), 'user does not have the right permissions'))
                    {{ $exception->getMessage() }}
                @else
                    Anda tidak memiliki izin untuk membuka halaman ini.
                @endif
            </p>
            <div class="hint">
                Role Anda dibatasi sesuai matriks RBAC (floor / ops / admin).
                Jika seharusnya punya akses, minta Super Admin meninjau role di
                <strong>Kelola Role</strong>, lalu login ulang.
            </div>
            <div class="actions">
                @auth
                    <a class="btn btn-primary" href="{{ route('dashboard') }}">Kembali ke dashboard</a>
                @else
                    <a class="btn btn-primary" href="{{ route('login') }}">Masuk</a>
                @endauth
                <a class="btn btn-secondary" href="javascript:history.back()">Halaman sebelumnya</a>
            </div>
            <p class="foot">Warehouse Management · NexWave Tech</p>
        </div>
    </div>
</body>
</html>
