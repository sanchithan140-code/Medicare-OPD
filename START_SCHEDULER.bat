@echo off
cd /d "%~dp0"
echo Keep this window open to process appointment reminders and weekly disease trends.
php artisan schedule:work
pause
