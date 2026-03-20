# 🚀 QUICK START GUIDE - Aplikasi Koperasi Digital

## ⚡ 5 Menit Setup!

### Step 1: Buat Database
```sql
CREATE DATABASE koperasi;
```

### Step 2: Clone Project (sudah done)
```bash
cd c:\laragon\www\koperasi
```

### Step 3: Run Setup Script
**Windows:**
```bash
setup.bat
```

**Atau manual:**
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
npm run build
```

### Step 4: Run Server
```bash
php artisan serve
```

### Step 5: Login
Buka browser: **http://localhost:8000**

**Login sebagai Admin:**
- Email: `admin@koperasi.com`
- Password: `password`

---

## 🎯 FIRST THINGS TO DO

### 1. Tambah Anggota Pertama
- Login sebagai Admin
- Menu **Anggota** → **Tambah Anggota**
- Isi data lengkap
- Klik **Simpan**

### 2. Input Simpanan
- Menu **Simpanan** → **Tambah Simpanan**
- Pilih anggota
- Pilih jenis: Pokok/Wajib/Sukarela
- Input nominal
- Klik **Simpan**

### 3. Ajukan Pinjaman
- Menu **Pinjaman** → **Tambah Pinjaman**
- Pilih anggota (yang belum punya pinjaman)
- Input:
  - Jumlah pinjaman (contoh: 5000000)
  - Bunga per tahun (contoh: 12)
  - Tenor dalam bulan (contoh: 12)
- Klik **Simpan**
- Status awal: **Pending**

### 4. Approve Pinjaman
- Menu **Pinjaman** → Klik nama anggota
- Klik tombol **Approve**
- System akan otomatis:
  - Generate jadwal angsuran (12 bulan)
  - Hitung cicilan per bulan
  - Update status jadi **Active**

### 5. Lihat Dashboard
- Menu **Dashboard**
- Lihat statistik real-time:
  - Total Anggota
  - Total Simpanan
  - Total Pinjaman
  - Angsuran Bulan Ini

### 6. Generate Laporan
- Menu **Laporan**
- Pilih:
  - Laporan Anggota
  - Laporan Simpanan
  - Laporan Pinjaman
  - Laporan Keuangan
- Klik **Export PDF** atau **Export Excel**

---

## 🎭 3 ROLE TERSEDIA

### 👑 Admin (Full Access)
- Email: `admin@koperasi.com`
- Bisa akses SEMUA fitur
- Approve/Reject pinjaman
- Manage users & roles
- View audit logs

### 💼 Pengurus (Operational)
- Email: `pengurus@koperasi.com`
- Manage anggota
- Input simpanan
- Process pinjaman & angsuran
- Generate laporan
- View audit logs

### 👤 Anggota (View Only)
- Email: `anggota@koperasi.com`
- View personal dashboard
- Lihat simpanan sendiri
- Lihat pinjaman sendiri
- Lihat jadwal angsuran
- **Tidak bisa** edit data

---

## 📊 MENU NAVIGATION

```
🏠 Dashboard
   ├─ Total Members
   ├─ Total Savings
   ├─ Total Loans
   └─ Monthly Installments

👥 Anggota
   ├─ Daftar Anggota
   ├─ Tambah Anggota
   ├─ Edit Anggota
   └─ Activate/Deactivate

💰 Simpanan
   ├─ Daftar Simpanan
   ├─ Tambah Simpanan (Pokok/Wajib/Sukarela)
   └─ History per Anggota

💳 Pinjaman
   ├─ Daftar Pinjaman
   ├─ Ajukan Pinjaman
   ├─ Approve/Reject
   └─ Status Tracking

📅 Angsuran
   ├─ Jadwal Angsuran
   ├─ Bayar Cicilan
   ├─ Overdue Tracking
   └─ Payment History

📈 Laporan
   ├─ Laporan Anggota (PDF/Excel)
   ├─ Laporan Simpanan (PDF/Excel)
   ├─ Laporan Pinjaman (PDF/Excel)
   └─ Laporan Keuangan (PDF/Excel)

🔍 Audit Log (Admin Only)
   └─ Track all user activities
```

---

## 💡 TIPS & TRICKS

### ✅ Best Practices

1. **Selalu backup database** sebelum test production
```bash
php artisan backup:run
```

2. **Test dengan data dummy** dulu sebelum real data
```bash
php artisan db:seed --class=DummyDataSeeder
```

3. **Clear cache** jika ada perubahan config
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

4. **Check error logs** di `storage/logs/laravel.log`

5. **Use version control** untuk tracking changes
```bash
git init
git add .
git commit -m "Initial commit"
```

---

## 🐛 COMMON ISSUES

### ❌ Tailwind tidak muncul
```bash
npm run build
# atau untuk dev:
npm run dev
```

### ❌ Permission denied
```bash
php artisan cache:clear
php artisan config:clear
composer dump-autoload
```

### ❌ Database error
```bash
# Check .env DB config
# Make sure database exists
php artisan migrate:fresh --seed
```

### ❌ Class not found
```bash
composer dump-autoload
php artisan clear-compiled
```

---

## 📞 BUTUH BANTUAN?

### 📚 Documentation
- `README.md` - Overview
- `INSTALLATION.md` - Instalasi lengkap
- `PROJECT_SUMMARY.md` - Summary fitur

### 🆘 Support
- Email: support@koperasi.com
- Phone: +62xxx
- WhatsApp: +62xxx

---

## 🎉 SELAMAT!

**Aplikasi Koperasi Digital Anda siap digunakan!**

Jangan lupa:
- ✅ Test all features
- ✅ Backup database regularly
- ✅ Monitor audit logs
- ✅ Train users
- ✅ Plan for Phase 2 (Premium features)

**Happy Managing! 🚀**
