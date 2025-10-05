@echo off
REM Start Vite/webpack dev server
start "npm run dev" cmd /k npm run dev

REM Start Laravel queue listener
start "php artisan queue:listen" cmd /k php artisan queue:listen

REM Start Laravel Reverb WebSocket server
start "php artisan reverb:start" cmd /k php artisan reverb:start

start "php artisan schedule:work" cmd /k php artisan schedule:work
