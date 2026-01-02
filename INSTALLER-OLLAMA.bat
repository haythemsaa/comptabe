@echo off
echo ============================================
echo Installation Automatique Ollama
echo ============================================
echo.
echo Ce script va installer Ollama automatiquement.
echo.
echo ATTENTION: Necessite les droits administrateur
echo.
pause
echo.

REM Exécuter le script PowerShell en tant qu'administrateur
PowerShell -NoProfile -ExecutionPolicy Bypass -Command "& {Start-Process PowerShell -ArgumentList '-NoProfile -ExecutionPolicy Bypass -File ""%~dp0installer-ollama.ps1""' -Verb RunAs}"

echo.
echo Le script PowerShell a ete lance.
echo Suivez les instructions dans la fenetre PowerShell qui s'est ouverte.
echo.
pause
