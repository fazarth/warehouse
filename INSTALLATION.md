# 🏦 APLIKASI KOPERASI DIGITAL - INSTALLATION GUIDE

## 📦 FITUR YANG SUDAH DIIMPLEMENTASI

### ✅ Fitur Dasar (BASIC Package)
- ✓ Login & Role Management (Admin, Pengurus, Anggota)
- ✓ Manajemen Data Anggota (CRUD)
- ✓ Simpanan (Pokok, Wajib, Sukarela)
- ✓ Pinjaman (Pengajuan, Approval, Rejection)
- ✓ Angsuran (Auto-generate, Payment tracking)
- ✓ Dashboard Analytics Real-time
- ✓ Laporan (Export PDF & Excel ready)
- ✓ Audit Log (Track all activities)
- ✓ Role & Permission (Spatie)

## 🛠️ INSTALASI LENGKAP

### 1. Install Dependencies
```bash
cd c:\laragon\www\koperasi
composer install
npm install
```

### 2. Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Konfigurasi Database (.env)
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=koperasi
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Migrate & Seed
```bash
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
```

### 5. Install Tailwind CSS
```bash
npm install -D tailwindcss postcss autoprefixer
npm install alpinejs
npm run build
```

### 6. Publish Spatie Permission
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

### 7. Link Storage
```bash
php artisan storage:link
```

### 8. Run Application
```bash
# Terminal 1: Laravel Server
php artisan serve

# Terminal 2: Vite Dev Server (jika development)
npm run dev
```

## 🔐 DEFAULT LOGIN CREDENTIALS

| Role      | Email                    | Password | Access Level           |
|-----------|--------------------------|----------|------------------------|
| Admin     | admin@koperasi.com       | password | Full System Access     |
| Pengurus  | pengurus@koperasi.com    | password | Operational Management |
| Anggota   | anggota@koperasi.com     | password | View Personal Data     |

## 📊 DATABASE TABLES

```
users                 - User authentication
roles                 - User roles (Spatie)
permissions          - Permissions (Spatie)
role_has_permissions - Role-Permission mapping
model_has_roles      - User-Role mapping
members              - Anggota koperasi
savings              - Transaksi simpanan
loans                - Data pinjaman
loan_installments    - Cicilan pinjaman
audit_logs           - Activity tracking
shu_distributions    - Pembagian SHU
member_shu           - SHU per member
packages             - Paket layanan
```

## 🎯 CARA MENGGUNAKAN

### 1. Login sebagai Admin
- Akses http://localhost:8000
- Login dengan: admin@koperasi.com / password

### 2. Tambah Anggota Baru
- Menu: **Anggota** → **Tambah Anggota**
- Isi form data anggota
- Status default: Active

### 3. Input Simpanan
- Menu: **Simpanan** → **Tambah Simpanan**
- Pilih anggota
- Pilih jenis: Pokok/Wajib/Sukarela
- Input jumlah

### 4. Ajukan Pinjaman
- Menu: **Pinjaman** → **Tambah Pinjaman**
- Pilih anggota
- Input: Jumlah, Bunga (%), Tenor (bulan)
- Status awal: Pending

### 5. Approve Pinjaman
- Menu: **Pinjaman** → Detail Pinjaman
- Klik **Approve**
- System otomatis generate jadwal angsuran

### 6. Bayar Angsuran
- Menu: **Angsuran**
- Pilih angsuran yang jatuh tempo
- Klik **Bayar**
- Input jumlah pembayaran

### 7. Lihat Laporan
- Menu: **Laporan**
- Pilih jenis laporan:
  - Laporan Anggota
  - Laporan Simpanan
  - Laporan Pinjaman
  - Laporan Keuangan
- Export ke PDF/Excel

## 🔧 KONFIGURASI LANJUTAN

### Install PDF Export (DomPDF)
```bash
composer require barryvdh/laravel-dompdf
```

Config di `config/app.php`:
```php
'providers' => [
    Barryvdh\DomPDF\ServiceProvider::class,
],
'aliases' => [
    'PDF' => Barryvdh\DomPDF\Facade::class,
]
```

### Install Excel Export (sudah installed)
```bash
composer require maatwebsite/excel
```

### Setup Redis Cache (Optional)
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## 🚀 PENGEMBANGAN SELANJUTNYA

### Premium Features (To Implement)
```php
// 1. Approval Workflow Multi-Level
php artisan make:controller ApprovalWorkflowController

// 2. Notifikasi WhatsApp
composer require twilio/sdk

// 3. Notifikasi Email
// Already configured in .env:
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
```

### Enterprise Features (To Implement)
```php
// 1. Multi-Branch
php artisan make:migration add_branch_support

// 2. Payment Gateway Midtrans
composer require midtrans/midtrans-php

// 3. PWA
npm install workbox-webpack-plugin
```

## 🐛 TROUBLESHOOTING

### 1. Error: Class not found
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### 2. Tailwind tidak muncul
```bash
npm run build
php artisan view:clear
hard refresh browser (Ctrl+Shift+R)
```

### 3. Permission denied error
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 4. Migration error
```bash
php artisan migrate:fresh
php artisan db:seed --class=RolePermissionSeeder
```

### 5. Vite not found
```bash
npm install
npm run build
```

## 📱 API ENDPOINTS (Untuk Mobile/PWA)

```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/member/profile', [ApiController::class, 'profile']);
    Route::get('/member/savings', [ApiController::class, 'savings']);
    Route::get('/member/loans', [ApiController::class, 'loans']);
    Route::get('/member/installments', [ApiController::class, 'installments']);
});
```

## 📞 SUPPORT & DOKUMENTASI

- **Documentation**: `/docs` folder
- **API Docs**: `/api/documentation`
- **Support Email**: support@koperasi.com

## 🎨 CUSTOMIZATION

### Mengubah Warna Tema
Edit `tailwind.config.js`:
```javascript
theme: {
  extend: {
    colors: {
      primary: '#1D4ED8',
      secondary: '#9333EA',
    }
  }
}
```

### Menambah Permission Baru
```php
use Spatie\Permission\Models\Permission;
Permission::create(['name' => 'custom permission']);

$role = Role::findByName('Admin');
$role->givePermissionTo('custom permission');
```

---

**🎉 Selamat! Aplikasi Koperasi Digital Anda siap digunakan!**

**Next Steps:**
1. ✅ Test semua fitur dasar
2. 📧 Configure email notifications
3. 💳 Setup payment gateway (Midtrans)
4. 📱 Implement PWA
5. 🔔 Add WhatsApp notifications
