# Warehouse Management

**Enterprise WMS** (Laravel 10 + Tailwind + Sanctum) — domain flagship portfolio project.

Inbound **PO → GR → QC → Put Away** · Outbound **FEFO pick → pack → ship** · **Returns / cycle count / aging** · **Mobile scan** · **ERP API** · stock per **bin+batch** · **StockMutation** ledger · **QR** registry.

> Recruiter one-pager: see **[PORTFOLIO.md](./PORTFOLIO.md)**  
> Pair project (.NET customs/bonded integration): `Live/bonded-inventory`

## Fitur

| Modul | Fitur |
|-------|--------|
| **Master** | Gudang, **Zone/Rak/Bin**, supplier, produk/SKU |
| **QR System** | Registry QR (`ITEM-`, `LOC-`, `WH-`, `SUP-`), scan console, label print |
| **PO / GR** | Purchase order → GR + QC → putaway ke bin |
| **Pengiriman (GI)** | DO pending → **FEFO allocate** → pick → pack → ship (deduct bin) → deliver |
| **Returns** | Customer/supplier return → receive ke bin + `StockMutation` type `return` |
| **Cycle Count** | Stockopname `mode=cycle` per bin → draft → pending_approval |
| **Aging** | Bucket FEFO/expiry (`expired`, `0-30`, `31-90`, `91-180`, `180+`, no-expiry) |
| **Mobile Scan** | `/m/scan` Zebra-friendly (input autofocus + lookup) |
| **ERP API** | Sanctum token: products/stock/PO/GR/GI/mutations/aging/scan |
| **Inventori** | Stok per gudang/batch/**bin**, mutasi stok |
| **Stock Opname** | Hitung fisik + penyesuaian (modul pro) |
| **Laporan** | Stok, penerimaan, pengiriman, mutasi, aging |
| **Admin** | User, role/permission, modul, settings |

## Roadmap (PRD) — complete

- ✅ **W1** Location hierarchy + QR registry + scan
- ✅ **W2** PO → QC → Put Away
- ✅ **W3** Pick (FEFO) → Pack → Ship/DO
- ✅ **W4** Returns, cycle count, aging reports
- ✅ **W5** Mobile scanner / Zebra
- ✅ **W6** ERP API integrations

## Domain invariants (why this is “enterprise-style”)

1. **All stock moves** create a `StockMutation` (ledger, not silent updates).
2. Stock is stored **per location (bin) + batch** (expiry-aware).
3. Outbound allocation is **FEFO** (`expired_date` ascending, nulls last).
4. QR codes are **registry-backed** and treated as scan keys for ops/API.
5. ERP consumers use **token API**; operator flows stay on web/mobile UI.

## Default Login (setelah seed)

| Role | Email | Password |
|------|-------|----------|
| Super Admin | `superadmin@fazarth.my.id` | `superadmin123!` |
| Admin Gudang | `admin.gudang@fazarth.my.id` | `password` |
| Supervisor | `supervisor@fazarth.my.id` | `password` |
| Operator | `operator@fazarth.my.id` | `password` |

> Ganti password di environment non-local.

## Quick Start

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# sesuaikan DB_* (default: mysql database `warehouse`)
php artisan migrate --seed
npm run build   # atau: npm run dev
php artisan serve --port=8000
# http://127.0.0.1:8000
```

### Smoke (regression)

```bash
python smoke_w1.py      # QR / locations
python smoke_w2.py      # PO → putaway
python smoke_w3.py      # FEFO GI
python smoke_w4w6.py    # returns/cycle/aging + mobile + ERP API
```

### Smoke QR manual
```
Login → Scan QR → coba: LOC-A-01-03 / ITEM-SKU-RICE-25 / SUP-001
Master → Zone/Rak/Bin → buka bin → Cetak Label
```

## Portfolio framing (for CV / interviews)

| Say this | Not this |
|----------|----------|
| Enterprise WMS with FEFO, bin stock, mutation ledger, ERP API | “Laravel CRUD gudang” |
| Same domain as warehouse/ERP production experience | “Side PHP project only” |
| Complement: .NET bonded inventory integration simulator | Stack war PHP vs C# |

See **PORTFOLIO.md** for architecture diagram, demo script, and talking points.
