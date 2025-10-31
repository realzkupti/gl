@echo off
echo Clearing all Laravel caches...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo Done! All caches cleared.
pause
