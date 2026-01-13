@echo off
echo ============================================
echo Deploy Backup System ke Railway
echo ============================================
echo.

echo [1/3] Staging files...
git add .

echo.
echo [2/3] Committing...
git commit -m "Add: Backup system + Maintenance mode

- Add BackupDatabase command (php artisan backup:database)
- Add RestoreDatabase command (php artisan backup:restore)
- Add backup documentation (BACKUP_SYSTEM.md)
- Add GitHub Actions backup reminder
- Maintenance page already created in previous commit

This prevents data loss disaster from happening again!"

echo.
echo [3/3] Pushing to Railway...
git push origin main

echo.
echo ============================================
echo DONE! Tunggu Railway deploy selesai (2-3 menit)
echo ============================================
echo.
echo Setelah deploy selesai:
echo 1. Buka Railway Dashboard
echo 2. Klik service - View Logs
echo 3. Klik icon Shell (terminal) di pojok kanan atas
echo 4. Jalankan: php artisan down --render="maintenance" --secret="recovery2026"
echo.
pause
