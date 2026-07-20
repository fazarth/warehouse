# Enterprise WMS — Portfolio Case Study

**Tezza Fazar Tri Hidayat** · Domain flagship  
**Repo:** `Live/warehouse` · Stack: Laravel 10, MySQL, Tailwind, Laravel Sanctum  
**Status:** W1–W6 closed (smoke package green)

---

## Problem

Warehouse operations often split across spreadsheets, ERP screens, and handheld scanners — stock accuracy breaks at **bin level**, outbound picks ignore expiry, and external systems cannot read a clean inventory API.

## Solution (what I built)

An **enterprise-style Warehouse Management System** with a single stock authority:

| Layer | Design choice |
|-------|----------------|
| **Stock truth** | All quantity changes go through `StockMutation` ledger |
| **Location model** | Warehouse → Zone → Rack → **Bin** |
| **Outbound** | **FEFO** allocate → pick → pack → ship (deduct on ship) |
| **Inbound** | PO → Goods Receipt → QC → **Put Away** to bin |
| **Ops** | Returns, cycle count, aging buckets, mobile scan |
| **Integration** | REST **ERP API** (token) for products/stock/docs/mutations/scan |

## Architecture (recruiter view)

```
[ Operator UI / Mobile /m/scan ]
            │
            ▼
[ Laravel Web + Policies ]
            │
   ┌────────┴────────┐
   ▼                 ▼
[ Domain Services ]  [ Sanctum ERP API ]
 Putaway / FEFO /
 Return / Aging / QR
   │
   ▼
[ ProductStock @ bin+batch ] ←→ [ StockMutation ledger ]
```

**Invariant:** never adjust stock without a mutation row. FEFO candidates order by `expired_date` (nulls last).

## Waves delivered

| Wave | Outcome |
|------|---------|
| **W1** | Location hierarchy + QR registry (`ITEM-`/`LOC-`/`WH-`/`SUP-`) + scan console |
| **W2** | PO → GR/QC → putaway tasks → bin stock |
| **W3** | GI: FEFO allocate → pick → pack → ship/deliver |
| **W4** | Customer/supplier returns · cycle count · aging report |
| **W5** | Zebra-friendly mobile scan (`/m/scan`) |
| **W6** | ERP API: login token + read endpoints + scan |

## Engineering highlights (interview talking points)

1. **Ledger over balances** — auditability for production support (matches real warehouse/ERP work).  
2. **FEFO as a service** — allocation creates pick lines; ship is the deduct boundary (no silent stock loss).  
3. **QR as first-class registry** — immutable codes for item/location/supplier.  
4. **API boundary** — Sanctum token for external ERP-style consumers; writes stay controlled on web.  
5. **Wave shipping** — sequential PRD with HTTP smoke scripts (regression safety).

## Stack honesty

| What | Why |
|------|-----|
| Laravel/PHP | Fast full-stack delivery for domain-complete WMS |
| MySQL | Relational stock + FK integrity |
| Tailwind | Operator-usable admin UI |

**Transfer to .NET jobs:** same patterns I use in C# enterprise apps — services, inventory correctness, API contracts, SQL-backed workflows (see also **Bonded Inventory Simulator** .NET project).

## Demo script (3 minutes)

1. Login operator → Scan `LOC-…` / `ITEM-…`  
2. Open GI → FEFO allocate → pick → pack → ship  
3. Aging report buckets  
4. `POST /api/auth/login` → `GET /api/stock` with Bearer token  

## Local run

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve --port=8000
```

Smoke: `python smoke_w1.py` · `smoke_w2.py` · `smoke_w3.py` · `smoke_w4w6.py`

## Credentials (local seed only)

| Role | Email | Password |
|------|-------|----------|
| Super Admin | `superadmin@fazarth.my.id` | `superadmin123!` |
| Operator | `operator@fazarth.my.id` | `password` |

Change passwords outside local demos.

## Metrics (engineering)

- PRD waves **W1–W6** complete  
- Multi-script HTTP smoke (W1→W6 package)  
- Modules: master, QR, PO/GR, GI FEFO, returns, cycle, aging, mobile, ERP API  

## Related portfolio piece

**Bonded / Customs Inventory Simulator** (.NET 9) — integration engine pattern (internal inventory ↔ mock customs portal), same logistics domain, public stack alignment for .NET roles.
