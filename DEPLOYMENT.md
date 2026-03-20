# 🚀 Panduan Deploy Aplikasi Koperasi Digital

## ❌ Vercel TIDAK Cocok untuk Laravel

Vercel dirancang untuk:
- Frontend (React, Vue, Next.js)
- Serverless Functions (Node.js)

Laravel membutuhkan:
- PHP runtime
- MySQL database
- Persistent storage
- Session management

---

## 🌐 Cloudflare Tunnel (GRATIS - Untuk Demo/Testing)

**Kelebihan:**
- ✅ 100% Gratis
- ✅ HTTPS otomatis
- ✅ Tidak perlu buka port router
- ✅ Domain gratis `*.trycloudflare.com`

**Kekurangan:**
- ⚠️ Komputer harus nyala 24/7
- ⚠️ Tidak cocok untuk production serius

### Step 1: Download Cloudflared

```powershell
# Windows (PowerShell Admin)
winget install Cloudflare.cloudflared

# Atau download manual dari:
# https://developers.cloudflare.com/cloudflare-one/connections/connect-apps/install-and-setup/installation/
```

### Step 2: Jalankan Laravel di Laragon
```bash
# Pastikan Laragon running dan Laravel bisa diakses di:
http://localhost:8000
# atau
http://koperasi.test (jika pakai virtual host)
```

### Step 3: Jalankan Cloudflare Tunnel (Quick Mode)
```powershell
# Cara tercepat - dapat URL random gratis
cloudflared tunnel --url http://localhost:8000

# Output akan seperti:
# Your quick Tunnel has been created! Visit it at:
# https://random-words-here.trycloudflare.com
```

### Step 4: (Opsional) Setup Tunnel Permanen dengan Domain Sendiri

```powershell
# 1. Login ke Cloudflare
cloudflared tunnel login

# 2. Buat tunnel baru
cloudflared tunnel create koperasi

# 3. Buat config file: ~/.cloudflared/config.yml
```

```yaml
# config.yml
tunnel: koperasi
credentials-file: C:\Users\<username>\.cloudflared\<tunnel-id>.json

ingress:
  - hostname: koperasi.yourdomain.com
    service: http://localhost:8000
  - service: http_status:404
```

```powershell
# 4. Route DNS
cloudflared tunnel route dns koperasi koperasi.yourdomain.com

# 5. Jalankan tunnel
cloudflared tunnel run koperasi
```

### Step 5: Jalankan sebagai Windows Service (Auto Start)
```powershell
# Install sebagai service (PowerShell Admin)
cloudflared service install

# Tunnel akan otomatis jalan saat Windows start
```

### ⚠️ Penting untuk Laravel!

Update `.env` agar Laravel tahu URL-nya:
```env
APP_URL=https://your-tunnel-url.trycloudflare.com
ASSET_URL=https://your-tunnel-url.trycloudflare.com
SESSION_SECURE_COOKIE=true
```

Update `config/app.php` jika perlu:
```php
'url' => env('APP_URL', 'http://localhost'),
'asset_url' => env('ASSET_URL', null),
```

---

## ✅ Alternatif Hosting yang Cocok

### 1. **Laravel Forge + DigitalOcean** (Recommended untuk Production)
**Harga**: ~$5-10/bulan

```bash
# Setup:
1. Buat akun di forge.laravel.com
2. Hubungkan dengan DigitalOcean/AWS/Linode
3. Deploy via Git push
```

### 2. **Railway.app** (Mudah & Murah)
**Harga**: Free tier tersedia, ~$5/bulan

```bash
# Setup:
1. Buat akun di railway.app
2. New Project → Deploy from GitHub
3. Add MySQL Service
4. Set environment variables

# File: railway.json
{
  "build": {
    "builder": "NIXPACKS"
  },
  "deploy": {
    "startCommand": "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT"
  }
}
```

### 3. **Render.com** (Gratis untuk Testing)
**Harga**: Free tier tersedia

```bash
# Setup:
1. Buat akun di render.com
2. New Web Service → Connect GitHub
3. Add PostgreSQL/MySQL database
4. Configure environment variables
```

### 4. **Shared Hosting Indonesia** (Paling Murah)
**Harga**: Rp 50.000-150.000/bulan

Rekomendasi:
- Niagahoster
- IDCloudHost  
- Domainesia
- Hostinger Indonesia

```bash
# Upload via:
1. FTP/FileManager
2. Upload file project (tanpa vendor, node_modules)
3. SSH ke server:
   composer install --optimize-autoloader --no-dev
   npm run build
   php artisan migrate --force
```

---

## 📦 Persiapan Sebelum Deploy

### 1. Buat file .env.production
```env
APP_NAME="Koperasi Digital"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=koperasi_prod
DB_USERNAME=your_username
DB_PASSWORD=your_secure_password

LICENSE_TYPE=full
```

### 2. Optimize untuk Production
```bash
# Di server:
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan db:seed --class=RolePermissionSeeder --force
```

### 3. Build Assets
```bash
npm run build
```

---

## 🔧 Langkah Deploy ke Shared Hosting (Niagahoster/Hostinger)

### Step 1: Persiapan Lokal
```bash
# Hapus cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Build assets
npm run build
```

### Step 2: Upload Files
```
Upload semua file KECUALI:
- /vendor (install di server)
- /node_modules (tidak perlu di server)
- .env (buat manual di server)
```

### Step 3: Setup di Server
```bash
# Via SSH:
cd public_html/koperasi
composer install --optimize-autoloader --no-dev
cp .env.example .env
# Edit .env dengan nano/vim

php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=RolePermissionSeeder --force
php artisan storage:link
```

### Step 4: Konfigurasi Document Root
```
Set document root ke: /public_html/koperasi/public
```

---

## 🐳 Deploy dengan Docker (Advanced)

### Dockerfile
```dockerfile
FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

RUN composer install --optimize-autoloader --no-dev
RUN php artisan config:cache
RUN php artisan route:cache

EXPOSE 9000
CMD ["php-fpm"]
```

### docker-compose.yml
```yaml
version: '3.8'
services:
  app:
    build: .
    ports:
      - "8000:8000"
    environment:
      - DB_HOST=db
    depends_on:
      - db
  
  db:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: koperasi
      MYSQL_ROOT_PASSWORD: secret
    volumes:
      - db_data:/var/lib/mysql

volumes:
  db_data:
```

---

## 📞 Rekomendasi Berdasarkan Kebutuhan

| Kebutuhan | Rekomendasi | Harga |
|-----------|-------------|-------|
| Testing/Demo | Railway.app | Gratis |
| Koperasi Kecil | Shared Hosting ID | Rp 50-100k/bln |
| Koperasi Menengah | DigitalOcean + Forge | $10-20/bln |
| Multi Cabang | AWS/GCP + Forge | $50+/bln |

---

## ✅ Quick Start - Railway.app (Paling Mudah)

1. Push code ke GitHub
2. Buka [railway.app](https://railway.app)
3. "New Project" → "Deploy from GitHub"
4. Pilih repository koperasi
5. Add "MySQL" service
6. Set Environment Variables:
   - `APP_KEY` = generate dengan `php artisan key:generate --show`
   - `DB_CONNECTION` = mysql
   - `DB_HOST` = dari MySQL service
   - `DB_DATABASE` = railway
   - `DB_USERNAME` = root
   - `DB_PASSWORD` = dari MySQL service
7. Deploy otomatis!

Link akan tersedia seperti: `https://koperasi-production.up.railway.app`
