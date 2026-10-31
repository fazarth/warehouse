@echo off
echo ========================================
echo  🏦 KOPERASI DIGITAL - Setup Script
echo ========================================
echo.

echo 📦 Installing Composer Dependencies...
call composer install --no-interaction --prefer-dist --optimize-autoloader

echo.
echo 📦 Installing NPM Dependencies...
call npm install

echo.
echo 🔑 Generating Application Key...
call php artisan key:generate

echo.
echo 📁 Publishing Spatie Permission...
call php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

echo.
echo 🗄️ Running Migrations...
call php artisan migrate --force

echo.
echo 🌱 Seeding Database with Roles and Permissions...
call php artisan db:seed --class=RolePermissionSeeder

echo.
echo 🔗 Creating Storage Link...
call php artisan storage:link

echo.
echo 🎨 Building Frontend Assets...
call npm run build

echo.
echo 🧹 Clearing Cache...
call php artisan config:clear
call php artisan cache:clear
call php artisan view:clear
call php artisan route:clear

echo.
echo ========================================
echo  ✅ Setup Complete!
echo ========================================
echo.
echo 🔐 Default Login Credentials:
echo    Admin    : admin@koperasi.com / password
echo    Pengurus : pengurus@koperasi.com / password
echo    Anggota  : anggota@koperasi.com / password
echo.
echo 🚀 To start the application (local APP_PORT default 8100):
echo    scripts\start-local.sh
echo    or: php artisan serve --host=127.0.0.1 --port=8100
echo.
echo 📱 Access at: http://127.0.0.1:8100
echo.
pause
