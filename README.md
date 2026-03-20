# 🏦 Koperasi Digital - Aplikasi Manajemen Koperasi

## ✨ Fitur yang Sudah Jadi

### 📋 Modul Inti (Basic Package)
| Modul | Status | Fitur |
|-------|--------|-------|
| ✅ **Dashboard** | Ready | Statistik, grafik, overview |
| ✅ **Anggota** | Ready | CRUD, aktivasi/deaktivasi, profil |
| ✅ **Simpanan** | Ready | Tambah simpanan, riwayat, jenis (pokok/wajib/sukarela) |
| ✅ **Pinjaman** | Ready | Pengajuan, approval, penolakan |
| ✅ **Angsuran** | Ready | Pembayaran, mark overdue |
| ✅ **Kas & Jurnal** | Ready | Kas masuk/keluar, jurnal transaksi |
| ✅ **Laporan** | Ready | Anggota, simpanan, pinjaman, keuangan |
| ✅ **Pengaturan** | Ready | Profil, password, app settings, finance |

### ⭐ Modul Pro Package
| Modul | Status | Fitur |
|-------|--------|-------|
| ✅ **SHU** | UI Ready | Perhitungan, distribusi (placeholder) |
| ✅ **Inventori** | UI Ready | Produk, stok (placeholder) |
| ✅ **Notifikasi** | UI Ready | WhatsApp, Email, template (placeholder) |

### 💎 Modul Full Package
| Modul | Status | Fitur |
|-------|--------|-------|
| ✅ **Multi Cabang** | UI Ready | Cabang management (placeholder) |

### 🔐 Admin & Security
| Fitur | Status |
|-------|--------|
| ✅ Role-Based Access Control (RBAC) | 6 roles dengan 50+ permissions |
| ✅ Kelola User | CRUD, assign role |
| ✅ Role & Permission Management | Assign/revoke permissions per role |
| ✅ Kelola Modul | Enable/disable modules |
| ✅ Package Switcher | Basic/Pro/Full license |
| ✅ Audit Log | Activity tracking |

---

## 👤 Default Login

| Role | Email | Password |
|------|-------|----------|
| Super Admin | superadmin@system.local | superadmin123! |
| Admin Koperasi | admin@koperasi.com | password |
| Ketua | ketua@koperasi.com | password |
| Bendahara | bendahara@koperasi.com | password |
| Petugas | petugas@koperasi.com | password |
| Anggota | anggota@koperasi.com | password |

---

## 🚀 Quick Start

### 1. Install Dependencies
```bash
composer install
npm install
```

### 2. Setup Database
```bash
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

### 3. Build Assets
```bash
npm run build
# atau untuk development:
npm run dev
```

### 4. Run Server
```bash
php artisan serve
# Akses: http://localhost:8000
```

### 5. (Optional) Cloudflare Tunnel
```bash
# Jalankan untuk akses dari internet
cloudflared tunnel --url http://localhost:8000
```

---

## 📁 Struktur Project

```
koperasi/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/           # User, Role, Module controllers
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── MemberController.php
│   │   ├── SavingController.php
│   │   ├── LoanController.php
│   │   ├── InstallmentController.php
│   │   ├── ReportController.php
│   │   └── SettingsController.php
│   ├── Models/
│   ├── Services/
│   │   ├── LicenseService.php
│   │   └── ModuleService.php
│   └── Helpers/
├── config/
│   ├── license.php          # License configuration
│   ├── packages.php          # Package definitions
│   └── modules.php           # Module definitions
├── database/
│   ├── migrations/
│   └── seeders/
│       └── RolePermissionSeeder.php
├── resources/
│   ├── views/
│   │   ├── layouts/app.blade.php
│   │   ├── dashboard.blade.php
│   │   ├── members/
│   │   ├── savings/
│   │   ├── loans/
│   │   ├── installments/
│   │   ├── finance/
│   │   ├── shu/
│   │   ├── inventory/
│   │   ├── branch/
│   │   ├── notification/
│   │   ├── reports/
│   │   ├── audit/
│   │   ├── settings/
│   │   └── admin/
│   └── css/app.css           # Tailwind + Custom CSS
├── routes/web.php
├── tailwind.config.js
├── start_tunnel.bat          # Cloudflare tunnel starter
└── DEPLOYMENT.md             # Deployment guide
```

---

## 🎨 Theme & Design

- **Primary Color**: Soft Green (#4ade80 - Koperasi branding)
- **Framework**: Tailwind CSS + Custom Components
- **Icons**: Emoji-based for simplicity
- **Animations**: Smooth transitions & micro-interactions
- **Responsive**: Mobile-first design

---

## 📋 TODO / Belum Diimplementasi

### Backend yang perlu dilengkapi:
- [ ] SHU calculation logic
- [ ] SHU distribution to members
- [ ] Inventory CRUD operations
- [ ] Multi-branch data isolation
- [ ] WhatsApp API integration
- [ ] Email notification sending
- [ ] Report PDF export
- [ ] Excel export for all modules
- [ ] Member portal (self-service)
- [ ] API endpoints for mobile app

### Nice to Have:
- [ ] Dark mode toggle
- [ ] Multi-language support
- [ ] Dashboard widgets customization
- [ ] Scheduled reports
- [ ] SMS notification
- [ ] Payment gateway integration (Midtrans)

---

## 📞 Support

Untuk pertanyaan atau bantuan, hubungi developer.

---

**Version**: 1.0.0  
**Last Updated**: January 2026
