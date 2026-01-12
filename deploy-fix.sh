#!/bin/bash

# Quick Deploy Script - Fix Performance & Database Recovery
# Run: bash deploy-fix.sh

echo "🚀 Starting deployment..."

# Stage all changes
git add .

# Commit with descriptive message
git commit -m "🔧 Critical Fix: Performance optimization + Database recovery + Maintenance mode

- Fix missing Cache & DB facade imports (500 error)
- Add performance optimizations (limits, caching, query optimization)
- Fix .nixpacks.toml - remove dangerous db:seed on deploy
- Add maintenance mode custom view
- Add database recovery guide
- Add missing imports in LaporanBulananController
- Add missing imports in LaporanTriwulananController  
- Add missing imports in LaporanTahunanController
- Add missing imports in ExportController

CRITICAL CHANGES:
✅ Remove 'php artisan db:seed --force' from .nixpacks.toml
✅ Now only runs 'php artisan migrate --force' (safe)
✅ Added Cache facade imports
✅ Added DB facade imports
✅ Performance optimizations applied
✅ Maintenance mode ready

NEXT STEPS:
1. Push to Railway: git push origin main
2. Enable maintenance: php artisan down --render=\"maintenance\" --secret=\"recovery2026\"
3. Run seeder: php artisan db:seed --class=StaffKuaBanjarmasinUtaraSeeder
4. Disable maintenance: php artisan up"

# Push to remote
echo ""
echo "📤 Pushing to Railway..."
git push origin main

echo ""
echo "✅ Deployment complete!"
echo ""
echo "🔧 NEXT STEPS:"
echo "1. Wait for Railway deployment to finish"
echo "2. Login to Railway Console/Shell"
echo "3. Enable maintenance mode:"
echo "   php artisan down --render=\"maintenance\" --secret=\"recovery2026\""
echo "4. Run database seeder:"
echo "   php artisan db:seed --class=StaffKuaBanjarmasinUtaraSeeder"
echo "5. Test via secret URL: https://www.kua-banjarmasin-utara.my.id/recovery2026"
echo "6. Disable maintenance when done:"
echo "   php artisan up"
echo ""
echo "📖 Full guide: See RECOVERY_DATABASE.md and ENABLE_MAINTENANCE_MODE.md"
