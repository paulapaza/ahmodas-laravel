@echo off
timeout /t 30 /nobreak >nul
cd /d C:\laragon\www\svp
"C:\laragon\bin\php\php-8.2.27-Win32-vs16-x64\php.exe" artisan reverb:start
pause
