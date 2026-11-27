@echo off
title SMART AHORRO - Orquestador Centralizado (Laravel + Spring Boot)
cd /d "%~dp0"

powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0..\scripts\start-smart-ahorro.ps1"

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [ERROR] El orquestador finalizo con codigo %ERRORLEVEL%.
    pause
)