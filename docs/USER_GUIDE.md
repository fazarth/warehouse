# User Guide — Enterprise WMS

**Versi:** local / demo freeze  
**URL lokal:** `http://127.0.0.1:8100`  
**Bahasa UI:** Indonesia  

Panduan ini disusun **per role** lalu **per flow**, supaya user tidak harus baca semuanya.

| Saya role… | Baca dulu |
|------------|-----------|
| Baru pertama kali | [§1 Mulai cepat](#1-mulai-cepat) |
| Operator (lantai gudang) | [§3 Operator](#3-operator--lantai-gudang) |
| Supervisor | [§4 Supervisor](#4-supervisor--verifikasi--pengawasan) |
| Admin Gudang | [§5 Admin Gudang](#5-admin-gudang--ops--master--user) |
| Super Admin | [§6 Super Admin](#6-super-admin--sistem) |
| Mau paham alur barang | [§2 Flow bisnis](#2-flow-bisnis-inti) |
| Error / 403 / lupa menu | [§8 Troubleshooting](#8-troubleshooting) |

Detail hak akses teknis: [`docs/RBAC.md`](./RBAC.md).

---

## 1. Mulai cepat

### 1.1 Login

1. Buka `http://127.0.0.1:8100/login`
2. Isi email + password
3. Setelah login → **Dashboard**

**Tidak ada registrasi publik.** User hanya bisa ditambah oleh **Super Admin / Admin Gudang** (menu Kelola User).

### 1.2 Akun demo (setelah seed)

| Role | Email | Password (lab) |
|------|-------|----------------|
| Super Admin | `superadmin@fazarth.my.id` | `superadmin123!` |
| Admin Gudang | `admin.gudang@fazarth.my.id` | `password` |
| Supervisor | `supervisor@fazarth.my.id` | `password` |
| Operator | `operator@fazarth.my.id` | `password` |

> Password di atas **hanya untuk local/demo**. Ganti sebelum dipakai orang lain / environment non-lab.

### 1.3 Ganti password sendiri

Semua role: **Akun Saya** → password (atau lewat menu settings akun yang tersedia).

### 1.4 Kenapa menu saya beda?

Sidebar **otomatis** mengikuti role + permission.  
Kalau menu tidak muncul / tombol hilang → biasanya **bukan bug**, tapi **tidak punya izin**. Lihat [§8](#8-troubleshooting).

---

## 2. Flow bisnis inti

Baca ringkas ini dulu biar flow di tiap role “nyambung”.

```text
[Master data]
  Gudang + Bin + Supplier + Produk
        │
        ▼
[PO] ──► [GR / Penerimaan] ──► [QC / Verify] ──► stok STAGING
        │                                         │
        │                                         ▼
        │                                   [Put Away] ──► stok di BIN (+ batch)
        │
        ▼
[GI / Pengiriman] ──► FEFO allocate ──► pick ──► pack ──► ship ──► deliver
        │
        ▼
  stok bin berkurang (StockMutation)

Opsional:
  Returns → terima ke bin
  Stock Opname / Cycle Count → hitung fisik → approve
  Laporan / Aging / Low stock → manajemen
```

### Aturan penting (jangan dilanggar mental model)

1. **Stok bergerak lewat transaksi** (GR verify, putaway, ship, return, SO approve) — bukan “edit angka stok” sembarangan.
2. Outbound alokasi **FEFO** (batch yang lebih dulu kadaluarsa lebih dulu).
3. Scan mobile (`/m/scan`) butuh **sudah login**; Operator bisa scan tanpa buka master produk.
4. **Import CSV** = master data saja (produk/supplier/gudang). **Tidak** menambah stok.

---

## 3. Operator — lantai gudang

### 3.1 Apa tugas saya?

Mencatat barang **masuk (buat GR)**, **pindah ke bin (putaway)**, **keluar (buat GI)**, hitung fisik, return, dan scan.

### 3.2 Yang boleh / tidak

| Boleh | Tidak boleh |
|-------|-------------|
| Dashboard, Scan, Mobile Scan | Master Gudang/Lokasi/Supplier/Produk |
| Buat & lihat GR | Verify/QC GR |
| Selesaikan Put Away | Master CRUD / PO |
| Buat GI (DO) | Ship GI / approve SO |
| SO / Cycle / Return (buat) | Laporan, Mutasi, Audit, User, Role |
| Lihat Stok Gudang | Import master, pengaturan aplikasi |

Kalau buka URL terlarang → **403**.

### 3.3 Flow harian Operator

#### A) Scan barang / lokasi

1. **Mobile Scan** (`/m/scan`) — cocok handheld/Zebra  
   atau **Scan QR** di menu desktop
2. Scan / ketik kode (`ITEM-…`, `LOC-…`, dll.)
3. Ikuti aksi yang muncul (hanya aksi yang diizinkan role)

#### B) Terima barang (buat GR saja)

1. Menu **Penerimaan Barang** → **Tambah** / Create  
2. Pilih supplier, tanggal, isi baris item (produk, gudang, qty, batch/exp bila ada)  
3. Simpan → status biasanya **pending**  
4. **Stop di sini.** QC/verify dikerjakan **Supervisor / Admin / Super**

#### C) Put Away (pindah staging → bin)

1. Menu **Put Away**  
2. Buka task **pending**  
3. Pilih **bin/lokasi** tujuan  
4. Selesaikan / complete  

Setelah sukses, stok ada di bin (bukan staging).

#### D) Kirim barang (buat GI saja)

1. Menu **Pengiriman Barang** → Create  
2. Isi tujuan, tanggal, item + gudang + qty  
3. Simpan → status **pending**  
4. **Allocate / pick / pack / ship** biasanya dilanjut Supervisor/Admin  
   (Operator fokus create + floor; ship butuh izin khusus)

#### E) Stock Opname / Cycle Count / Return

| Modul | Operator |
|-------|----------|
| Stock Opname | Buat + submit (bila ada) |
| Cycle Count | Buat + isi hitungan + submit |
| Returns | Buat return (receive bisa role yang punya izin) |

**Approve SO** = Supervisor/Admin, bukan Operator.

### 3.4 Checklist shift Operator

- [ ] Login OK  
- [ ] Mobile scan bisa dipakai  
- [ ] GR pending yang saya buat tercatat  
- [ ] Putaway task selesai sesuai instruksi  
- [ ] GI yang diminta sudah dibuat  
- [ ] Logout di perangkat bersama  

---

## 4. Supervisor — verifikasi & pengawasan

### 4.1 Apa tugas saya?

Mengawasi kualitas & pengiriman: **verify GR**, **ship GI**, **approve SO**, lihat master (read-only), dan **laporan**.

### 4.2 Yang boleh / tidak

| Boleh | Tidak boleh |
|-------|-------------|
| Lihat master (gudang/produk/dll.) | Create/edit/delete master |
| Lihat PO | Create/edit PO |
| Verify GR, complete putaway | (umumnya) create GR/GI — sesuai matrix: create GR/GI **tidak** untuk Supervisor |
| Ship GI, approve/reject SO | Kelola User / Role / Modul / Audit |
| Laporan + export CSV/Excel/PDF | Pengaturan Aplikasi |

> Ringkas: Supervisor = **oversight**, bukan data-entry master.

### 4.3 Flow utama Supervisor

#### A) QC / Verify penerimaan

1. **Penerimaan Barang** → buka GR **pending**  
2. Isi qty lulus/gagal QC (atau skip QC bila diizinkan proses internal)  
3. **Verify**  
4. Sistem: stok masuk **staging** + spawn **putaway task** (bila ada qty lulus)

#### B) Pastikan putaway jalan

1. **Put Away** → pantau pending  
2. Bisa complete sendiri atau minta Operator  
3. Stok harus pindah ke **bin** sebelum diandalkan untuk FEFO keluar

#### C) Pipeline pengiriman

1. Buka **Pengiriman Barang** (GI)  
2. Alur ideal: **Allocate (FEFO)** → **Pick** → **Pack** → **Ship**  
3. **Ship** mengurangi stok bin  
4. **Deliver** (konfirmasi sampai) bila dipakai

#### D) Approve stock opname / cycle

1. Buka SO / cycle berstatus **pending_approval**  
2. Cek selisih fisik vs sistem  
3. **Approve** (stok disesuaikan) atau **Reject**

#### E) Laporan manajemen

Menu **Laporan** / **Aging** / **Low Stock**:

1. Filter tanggal / gudang bila perlu  
2. Export **CSV / Excel / PDF**  
3. Operator **tidak** punya menu ini

### 4.4 Checklist Supervisor

- [ ] Tidak ada GR pending menumpuk tanpa QC  
- [ ] Putaway pending wajar  
- [ ] GI open dipantau sampai ship  
- [ ] SO pending_approval diputus hari yang sama bila memungkinkan  
- [ ] Low stock / near expiry dicek berkala  

---

## 5. Admin Gudang — ops + master + user

### 5.1 Apa tugas saya?

Pemilik operasional gudang: master data, PO, full ops, user gudang, settings app, laporan.  
**Tanpa** mapping Role/Modul sistem & **tanpa** Audit Log (itu Super Admin).

### 5.2 Yang boleh / tidak

| Boleh | Tidak |
|-------|-------|
| Semua ops (create + verify + ship, dll.) | Kelola Role |
| Master CRUD + **Import** produk/supplier/gudang | Kelola Modul |
| PO create/edit | Audit Log |
| Kelola User (bukan Super Admin) | — |
| Pengaturan Aplikasi | — |
| Semua laporan + export | — |

### 5.3 Flow setup awal (sekali / jarang)

#### A) Master gudang & bin

1. **Gudang** → tambah gudang aktif  
2. **Zone / Rak / Bin** → susun lokasi bin (scan/FEFO butuh bin aktif)  
3. Opsional: **Import Gudang** (CSV) = master gudang saja, **tidak** auto-buat bin

#### B) Supplier & produk

1. **Supplier** → create atau **Import CSV/XLSX**  
2. **Produk** → create atau **Import**  
3. Unduh **template** dulu dari halaman import  
4. Centang “update jika sudah ada” hanya bila sengaja overwrite

**Import tidak menambah stok.** Stok hanya lewat GR/return/SO, dll.

#### C) User gudang

1. **Kelola User** → Tambah  
2. Pilih role: Admin Gudang / Supervisor / Operator (bukan Super, kecuali Anda Super)  
3. Beri tahu user untuk ganti password  

### 5.4 Flow inbound lengkap (Admin bisa end-to-end)

1. (Opsional) **Purchase Order** buat & set status siap terima  
2. **GR** create (bisa taut PO)  
3. **Verify/QC**  
4. **Put Away** ke bin  
5. Cek **Stok Gudang** / **Mutasi Stok**

### 5.5 Flow outbound lengkap

1. Pastikan stok **di bin** (bukan cuma staging)  
2. **GI** create  
3. **Allocate FEFO** → pick → pack → **ship**  
4. Deliver bila perlu  
5. Export laporan pengiriman bila diminta management  

### 5.6 Import cepat

| Data | Menu | Template |
|------|------|----------|
| Produk | Produk → Import | `/products/import-template` |
| Supplier | Supplier → Import | `/suppliers/import-template` |
| Gudang | Gudang → Import | `/warehouses/import-template` |

Format: **CSV atau Excel**. Baris gagal muncul di pesan “baris dilewati”.

### 5.7 Checklist Admin

- [ ] Master aktif (gudang, bin, supplier, SKU)  
- [ ] User role benar  
- [ ] Inbound: tidak macet di pending QC / putaway  
- [ ] Outbound: FEFO + ship tercatat  
- [ ] Low stock & aging dipantau  
- [ ] Password demo sudah diganti di non-lab  

---

## 6. Super Admin — sistem

### 6.1 Apa tugas saya?

Segala yang Admin bisa **plus**:

- **Kelola Role** (permission mapping)
- **Kelola Modul** (on/off modul / lisensi)
- **Audit Log** (jejak sistem) + export

### 6.2 Audit Log

1. Menu **Administrasi → Audit Log**  
2. Filter: search, action, user, tanggal  
3. Klik **Lihat** untuk old/new values  
4. Export **CSV / Excel / PDF**

**Hanya Super Admin.** Role lain = 403.

Contoh action yang tercatat:

- Login / logout / login gagal  
- User create/update/delete  
- Ubah permission role, toggle modul  
- Ops: GR/GI/putaway/SO/cycle/return  
- Master create/import  
- Export laporan  

Password di log **di-redact**.

### 6.3 Role & modul

| Menu | Kapan dipakai |
|------|----------------|
| Kelola Role | Sesuaikan permission (hati-hati: matrix seed = baseline ketat) |
| Kelola Modul | Nonaktifkan modul di luar lisensi / paket |

Setelah ubah permission besar: minta user **logout/login** ulang.

### 6.4 Checklist Super Admin

- [ ] Tidak membagikan akun Super ke operator  
- [ ] Audit dicek jika ada insiden  
- [ ] Role mapping tidak “longgar” ke Operator tanpa sengaja  
- [ ] Modul & lisensi sesuai environment  
- [ ] Sebelum production: `APP_DEBUG=false`, password diganti, HTTPS  

---

## 7. Ringkasan “siapa ngerjain apa” (satu halaman)

| Langkah | Operator | Supervisor | Admin | Super |
|---------|:--------:|:----------:|:-----:|:-----:|
| Master / import | ❌ | lihat | ✅ | ✅ |
| PO create | ❌ | lihat | ✅ | ✅ |
| GR create | ✅ | ❌* | ✅ | ✅ |
| GR verify | ❌ | ✅ | ✅ | ✅ |
| Putaway complete | ✅ | ✅ | ✅ | ✅ |
| GI create | ✅ | ❌* | ✅ | ✅ |
| GI ship | ❌ | ✅ | ✅ | ✅ |
| SO approve | ❌ | ✅ | ✅ | ✅ |
| Laporan export | ❌ | ✅ | ✅ | ✅ |
| Kelola user | ❌ | ❌ | ✅ | ✅ |
| Role / modul / audit | ❌ | ❌ | ❌ | ✅ |

\*Sesuai matrix RBAC ketat saat seed: Supervisor oversight, bukan floor create.

### Alur rekomended tim kecil

```text
Admin     : master + PO + user
Operator  : GR create + putaway + GI create + scan
Supervisor: verify GR + ship GI + approve SO + laporan
Super     : audit + role (jarang sentuh harian)
```

---

## 8. Troubleshooting

| Gejala | Arti / solusi |
|--------|----------------|
| Menu tidak ada | Role tidak punya permission — cek § role Anda |
| **403** | URL/tombol di luar izin — normal |
| Tidak bisa register | By design — minta Admin/Super tambah user |
| Tombol Verify/Ship tidak muncul | Butuh role Supervisor/Admin/Super |
| Stok tidak cukup saat GI | Belum putaway ke bin / qty bin kurang / batch tidak cocok FEFO |
| Import “sukses” tapi stok 0 | Normal — import master only |
| Import baris dilewati | SKU/kode duplikat atau validasi gagal — baca daftar error |
| Setelah reseed “aneh” | **Logout lalu login** lagi |
| Audit kosong | Belum ada event / filter terlalu sempit; login Super dulu |
| Port salah | Lokal kanonik **8100**, bukan 8000 |

### Pesan error yang “benar”

Sistem diarahkan menampilkan pesan manusiawi (validasi, 403, stok kurang).  
Kalau muncul stack trace panjang di browser → biasanya `APP_DEBUG=true` (lab). Di production harus `false`.

---

## 9. Istilah singkat

| Istilah | Arti |
|---------|------|
| **GR** | Goods Receipt — penerimaan |
| **GI** | Goods Issue — pengiriman / DO |
| **PO** | Purchase Order |
| **Put Away** | Pindah dari staging ke bin |
| **FEFO** | First Expired First Out |
| **Staging** | Stok lolos QC belum di bin |
| **Bin** | Lokasi simpan paling detail |
| **StockMutation** | Ledger setiap gerak stok |
| **Cycle count** | Hitung fisik per area/bin |
| **Aging** | Laporan kedekatan expired |

---

## 10. Referensi cepat di repo

| Dokumen | Isi |
|---------|-----|
| [`docs/RBAC.md`](./RBAC.md) | Matrix permission & menu |
| [`README.md`](../README.md) | Fitur, seed login, quick start |
| [`DEPLOYMENT.md`](../DEPLOYMENT.md) | Deploy + checklist production |
| [`SECURITY.md`](../SECURITY.md) | Catatan keamanan portfolio |
| `php tools/smoke_wms_residual.php` | Smoke residual teknis (IT) |

---

## 11. Batas panduan ini

- Ditulis untuk **aplikasi local/demo freeze** yang sudah di-verify.  
- **Production public** masih butuh hardening config (HTTPS, `APP_DEBUG=false`, ganti password, backup) — lihat `DEPLOYMENT.md` / `SECURITY.md`.  
- Bukan manual API ERP mendalam (lihat modul API / PORTFOLIO bila perlu).

---

**TL;DR untuk user baru**

1. Login dengan role Anda.  
2. Kerjakan hanya menu yang terlihat.  
3. Operator = buat & lantai; Supervisor = approve/verify/ship/laporan; Admin = master+ops+user; Super = role+modul+audit.  
4. Barang masuk: **GR → Verify → Putaway**. Barang keluar: **GI → FEFO → Ship**.  
5. 403 = salah role, bukan “sistem rusak”.
