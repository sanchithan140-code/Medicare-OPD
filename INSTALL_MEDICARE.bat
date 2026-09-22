@echo off
setlocal
cd /d "%~dp0"
echo =====================================================
echo MEDICARE Laravel 12 - XAMPP setup
 echo =====================================================
where php >nul 2>nul || (echo PHP was not found. Add C:\xampp\php to PATH or run C:\xampp\php\php.exe manually.& pause & exit /b 1)
where composer >nul 2>nul || (echo Composer was not found. Install Composer and select C:\xampp\php\php.exe.& pause & exit /b 1)
if not exist .env copy .env.example .env
call composer install
if errorlevel 1 (echo Composer installation failed.& pause & exit /b 1)
php artisan key:generate --force
php artisan storage:link
php artisan optimize:clear
php scripts\check_environment.php
echo.
echo Import the separate MEDICARE_LARAVEL_DATABASE_WITH_DEMO.sql in phpMyAdmin if not already imported.
echo Then edit .env and add SMTP, Gemini and optional Claude credentials.
echo Open http://localhost/medicare/
pause
