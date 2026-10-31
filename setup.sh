#!/usr/bin/env bash

echo "🏦 KOPERASI DIGITAL - Setup Script"
echo "=================================="
echo ""

echo "📦 Installing Composer Dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader

echo ""
echo "📦 Installing NPM Dependencies..."
npm install

echo ""
echo "🔑 Generating Application Key..."
php artisan key:generate

echo ""
echo "📁 Publishing Spatie Permission..."
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="permission-migrations"

echo ""
echo "🗄️ Running Migrations..."
php artisan migrate --force

echo ""
echo "🌱 Seeding Database with Roles & Permissions..."
php artisan db:seed --class=RolePermissionSeeder

echo ""
echo "🔗 Creating Storage Link..."
php artisan storage:link

echo ""
echo "🎨 Building Frontend Assets..."
npm run build

echo ""
echo "🧹 Clearing Cache..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo ""
echo "✅ Setup Complete!"
echo ""
echo "🔐 Default Login Credentials:"
echo "   Admin    : admin@koperasi.com / password"
echo "   Pengurus : pengurus@koperasi.com / password"
echo "   Anggota  : anggota@koperasi.com / password"
echo ""
echo "🚀 To start the application (local APP_PORT default 8100):"
echo "   ./scripts/start-local.sh"
echo "   # or: php artisan serve --host=127.0.0.1 --port=\${APP_PORT:-8100}"
echo ""
echo "📱 Access at: http://127.0.0.1:8100"
