@echo off
REM Agar jalan otomatis saat laptop menyala: tekan Win+R, ketik shell:startup,
REM lalu buat shortcut file ini di folder yang terbuka.
cd /d "%~dp0"
start "LARAS Web" /min cmd /k php artisan serve --host=0.0.0.0 --port=8000
start "LARAS Peringatan WA" /min cmd /k php artisan schedule:work