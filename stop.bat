@echo off
title SMART AHORRO - Detencion Controlada
cd /d "%~dp0"

powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\stop-smart-ahorro.ps1"