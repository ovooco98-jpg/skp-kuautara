#!/bin/bash

# Render Setup Script
# Script ini membantu setup awal setelah deployment ke Render

echo "🚀 Render Setup Script"
echo "======================"
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo "❌ File .env tidak ditemukan!"
    echo "   Pastikan environment variables sudah di-set di Render dashboard"
    exit 1
fi

echo "✅ File .env ditemukan"
echo ""

# Generate APP_KEY if not set
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Generating APP_KEY..."
    php artisan key:generate --force
    echo "✅ APP_KEY generated"
else
    echo "✅ APP_KEY sudah ada"
fi
echo ""

# Run migrations
echo "📊 Running migrations..."
php artisan migrate --force
echo "✅ Migrations completed"
echo ""

# Seed database (optional)
read -p "🌱 Seed database dengan StaffKuaBanjarmasinUtaraSeeder? (y/n) " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan db:seed --class=StaffKuaBanjarmasinUtaraSeeder --force
    echo "✅ Database seeded"
fi
echo ""

# Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link
echo "✅ Storage link created"
echo ""

# Optimize for production
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✅ Optimization completed"
echo ""

# Set permissions
echo "🔐 Setting permissions..."
chmod -R 775 storage bootstrap/cache
echo "✅ Permissions set"
echo ""

echo "🎉 Setup selesai!"
echo ""
echo "📝 Next steps:"
echo "   1. Check environment variables di Render dashboard"
echo "   2. Test aplikasi di URL yang diberikan Render"
echo "   3. Setup email SMTP jika perlu"
echo "   4. Setup custom domain (opsional)"
echo ""

