@echo off
cd /d "%~dp0"
if exist "C:\xampp\php\php.exe" (
 "C:\xampp\php\php.exe" buat-admin.php
) else (
 php buat-admin.php
)
pause
