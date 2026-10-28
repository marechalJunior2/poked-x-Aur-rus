@echo off
title Servidor Localhost Pokedex
cd /d "%~dp0"
echo ========================================================
echo    INICIANDO SIMULADOR LOCALHOST DA POKEDEX...
echo ========================================================
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0start_server.ps1"
pause
