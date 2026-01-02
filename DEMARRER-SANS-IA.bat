@echo off
title ComptaBE - Mode Sans IA
color 0E

echo.
echo ========================================
echo ComptaBE - Mode OCR Sans IA
echo ========================================
echo.
echo ATTENTION: Mode degrade - OCR classique uniquement
echo.
echo Ce qui fonctionne:
echo - Upload documents
echo - OCR Tesseract
echo - Extraction basique
echo - Creation manuelle factures
echo.
echo Ce qui NE fonctionne PAS:
echo - Amelioration IA
echo - Matching fournisseurs intelligent
echo - Auto-creation avec haute confiance
echo.
echo Precision attendue: 70-75%% (au lieu de 89%%)
echo.
echo ========================================
echo.

set /p continuer="Continuer en mode sans IA? (O/n): "
if /i "%continuer%"=="n" exit /b 0

echo.
echo Demarrage Queue Worker (OCR uniquement)...
echo.

start "Queue Worker - OCR" cmd /k "title Queue Worker OCR && color 0E && cd /d C:\laragon\www\compta && echo Mode Sans IA - OCR Classique && echo. && php artisan queue:work --queue=documents"

timeout /t 2 /nobreak >nul

echo.
echo ========================================
echo Service demarre!
echo ========================================
echo.
echo Ouverture du scanner...
start http://localhost/scanner

timeout /t 2 /nobreak >nul

start http://localhost/ocr/analytics

echo.
echo ========================================
echo PRET - Mode OCR Classique
echo ========================================
echo.
echo Scanner: http://localhost/scanner
echo Analytics: http://localhost/ocr/analytics
echo.
echo IMPORTANT:
echo - Verifiez TOUTES les donnees extraites
echo - La precision sera plus faible (70-75%%)
echo - Utilisez des PDF natifs pour de meilleurs resultats
echo.
echo Pour passer au mode avec IA:
echo 1. Installez Ollama depuis https://ollama.com/download
echo 2. Lancez: DEMARRAGE-SIMPLE.bat
echo.
pause
