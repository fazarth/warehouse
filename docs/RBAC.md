# WMS RBAC Matrix (tight)

Source of truth:
- Grants: `database/seeders/RolePermissionSeeder.php`
- Menu: `config/menu.php` (permission + role filter via `ModuleService`)
- Routes: `routes/web.php` middleware `permission:*`
- UI buttons: Blade `@can(...)` on create/edit/delete/verify/ship/approve

**Vendor login: tidak ada.**

## Role intent

| Role | Intent |
|------|--------|
| **Super Admin** | Full + role/module mapping |
| **Admin Gudang** | Full ops + master CRUD + PO + users + app settings |
| **Supervisor** | View + verify/ship/approve + reports (no master CRUD, no PO create, no users) |
| **Operator** | Floor only: scan, GR/GI create, putaway, SO/return/cycle create, stock view |

## Menu matrix (sidebar)

| Menu | Super | Admin | Supervisor | Operator |
|------|:-----:|:-----:|:----------:|:--------:|
| Dashboard / Scan / Mobile Scan | ✅ | ✅ | ✅ | ✅ |
| Master Gudang/Lokasi/Supplier/Produk | ✅ | ✅ | ✅ view | ❌ |
| Purchase Order | ✅ | ✅ | ✅ view | ❌ |
| GR / Putaway / GI / SO / Return / Cycle | ✅ | ✅ | ✅ | ✅ |
| Stok Gudang | ✅ | ✅ | ✅ | ✅ |
| Mutasi Stok | ✅ | ✅ | ✅ | ❌ |
| Laporan / Aging / Low Stock | ✅ | ✅ | ✅ | ❌ |
| Akun Saya | ✅ | ✅ | ✅ | ✅ |
| Pengaturan Aplikasi | ✅ | ✅ | ❌ | ❌ |
| Kelola User | ✅ | ✅ | ❌ | ❌ |
| Kelola Role / Modul | ✅ | ❌ | ❌ | ❌ |

## Action matrix

| Action | Super | Admin | Supervisor | Operator |
|--------|:-----:|:-----:|:----------:|:--------:|
| Master create/edit/delete | ✅ | ✅ | ❌ | ❌ |
| PO create/edit | ✅ | ✅ | ❌ (view only) | ❌ |
| GR create | ✅ | ✅ | ❌ | ✅ |
| GR verify | ✅ | ✅ | ✅ | ❌ |
| GI create | ✅ | ✅ | ❌ | ✅ |
| GI ship | ✅ | ✅ | ✅ | ❌ |
| Putaway complete | ✅ | ✅ | ✅ | ✅ |
| SO create / submit | ✅ | ✅ | ❌ | ✅ |
| SO approve/reject | ✅ | ✅ | ✅ | ❌ |
| Report / export | ✅ | ✅ | ✅ | ❌ |

## Operator permission list (exact)

Floor only — **no** `product.view` / `location.view` (master pages closed).

`goods_receipt.view|create`, `putaway.view|complete`, `goods_issue.view|create`, `stockopname.view|create`, `return.view|create`, `cycle_count.view|create`, `inventory.view`, `setting.view`

Scan `/m/scan` resolves product/location via **auth session + direct model lookup** (not master list routes).

## Reseed

```bash
php artisan db:seed --class=RolePermissionSeeder --force
php artisan permission:cache-reset
php artisan config:clear
```
