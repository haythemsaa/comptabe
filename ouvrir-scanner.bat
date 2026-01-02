@echo off
echo ============================================
echo Ouverture Scanner OCR - ComptaBE
echo ============================================
echo.

echo Verification si Ollama tourne...
curl -s http://localhost:11434/api/tags >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ERREUR: Ollama ne repond pas !
    echo.
    echo Verifiez que vous avez lance: ollama serve
    echo.
    pause
    exit /b 1
)

echo OK - Ollama tourne
echo.

echo Ouverture des pages dans le navigateur...
echo.

REM Ouvrir le scanner
start http://localhost/scanner

REM Attendre 1 seconde
timeout /t 1 /nobreak >nul

REM Ouvrir le dashboard analytics
start http://localhost/ocr/analytics

echo.
echo ============================================
echo Pages ouvertes:
echo - Scanner OCR: http://localhost/scanner
echo - Analytics: http://localhost/ocr/analytics
echo ============================================
echo.

pause
