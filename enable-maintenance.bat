@echo off
echo ============================================
echo Deploy MAINTENANCE MODE ke Railway
echo ============================================
echo.
echo File yang akan di-push:
echo - routes/web.php (maintenance mode)
echo - routes/web.php.backup (backup route asli)
echo - app/Console/Commands/BackupDatabase.php
echo - app/Console/Commands/RestoreDatabase.php
echo - BACKUP_SYSTEM.md
echo.

echo [1/3] Staging files...
git add routes/web.php routes/web.php.backup app/Console/Commands/*.php BACKUP_SYSTEM.md MAINTENANCE_ENV_GUIDE.md app/Http/Middleware/CheckMaintenanceMode.php bootstrap/app.php resources/views/maintenance.blade.php .github/workflows/backup-reminder.yml

echo.
echo [2/3] Committing...
git commit -m "Enable MAINTENANCE MODE

- All routes now show maintenance page
- Backup original routes to web.php.backup
- Add BackupDatabase and RestoreDatabase commands
- Add backup documentation
- Bypass URL: /recovery2026 (set cookie for 7 days)

Website will show maintenance page to all users.
Admin can bypass via: https://www.kua-banjarmasin-utara.my.id/recovery2026"

echo.
echo [3/3] Pushing to Railway...
git push origin main

echo.
echo ============================================
echo SELESAI! Website akan masuk MAINTENANCE MODE
echo ============================================
echo.
echo Tunggu Railway deploy selesai (2-3 menit)
echo.
echo Setelah deploy:
echo 1. Buka: https://www.kua-banjarmasin-utara.my.id
echo    (akan muncul halaman maintenance)
echo.
echo 2. Untuk akses admin:
echo    https://www.kua-banjarmasin-utara.my.id/recovery2026
echo    (cookie berlaku 7 hari)
echo.
echo 3. Untuk disable maintenance nanti:
echo    Jalankan: disable-maintenance.bat
echo.
pause
