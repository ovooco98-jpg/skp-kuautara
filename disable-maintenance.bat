@echo off
echo ============================================
echo DISABLE Maintenance Mode
echo ============================================
echo.

echo [1/4] Restore routes asli...
Copy-Item routes\web.php.backup -Destination routes\web.php -Force
echo Route asli berhasil di-restore!

echo.
echo [2/4] Staging files...
git add routes/web.php

echo.
echo [3/4] Committing...
git commit -m "Disable maintenance mode - Restore normal routes"

echo.
echo [4/4] Pushing to Railway...
git push origin main

echo.
echo ============================================
echo SELESAI! Maintenance mode dimatikan
echo ============================================
echo.
echo Tunggu Railway deploy selesai (2-3 menit)
echo Website akan kembali normal untuk semua user
echo.
pause
