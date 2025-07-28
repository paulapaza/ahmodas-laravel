@echo off

timeout /t 10 /nobreak > NUL

@echo off
cd /d C:\laragon\www\svp
php artisan reverb:start
