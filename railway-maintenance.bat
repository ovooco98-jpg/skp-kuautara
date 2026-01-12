@echo off
echo ============================================
echo Setup Railway CLI dan Enable Maintenance
echo ============================================
echo.

echo [1/5] Install Railway CLI...
npm install -g @railway/cli

echo.
echo [2/5] Login ke Railway...
railway login

echo.
echo [3/5] Link ke project...
railway link

echo.
echo [4/5] Enable maintenance mode...
railway run php artisan down --render="maintenance" --secret="recovery2026" --refresh=15

echo.
echo ============================================
echo DONE! Maintenance mode aktif
echo ============================================
echo.
echo Untuk disable nanti:
echo   railway run php artisan up
echo.
pause
