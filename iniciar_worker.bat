@echo off
title Worker de Ahmodas - SUNAT
color 0A
echo Iniciando Procesador de Colas de Laravel
echo Proyecto: Ahmodas (Envíos SUNAT diferidos por 1 hora)
echo [!] Optimizado para Servidor Local (Sleep 5 min)
echo ==========================================================
echo.
echo Presiona Ctrl+C para detener el proceso.
echo.
cmd /c "cd /d %~dp0 && php artisan queue:work --timeout=60 --tries=3 --sleep=300"
pause
