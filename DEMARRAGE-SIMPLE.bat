@echo off
title ComptaBE - Demarrage Simple
color 0B

echo.
echo ========================================
echo Demarrage Systeme OCR - Mode Simple
echo ========================================
echo.

REM Vérifier Ollama
where ollama >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    color 0C
    echo [ERREUR] Ollama non installe!
    echo.
    echo SOLUTION:
    echo 1. Allez sur: https://ollama.com/download
    echo 2. Telechargez OllamaSetup.exe
    echo 3. Installez-le (Next, Next, Install)
    echo 4. Dans un terminal, tapez: ollama pull llama3.1
    echo.
    pause
    exit /b 1
)

echo [OK] Ollama installe
echo.

REM Vérifier le modèle
ollama list | findstr "llama" >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    color 0E
    echo [ATTENTION] Aucun modele trouve!
    echo.
    echo Telechargement du modele llama3.1 (2.5 GB)...
    echo.
    set /p choix="Continuer? (O/n): "
    if /i "%choix%"=="n" exit /b 1

    echo.
    echo Telechargement en cours...
    ollama pull llama3.1

    if %ERRORLEVEL% NEQ 0 (
        echo.
        echo [ERREUR] Telechargement echoue
        echo.
        echo ALTERNATIVE - Modele plus petit (700 MB):
        echo ollama pull llama3.2:1b
        echo.
        pause
        exit /b 1
    )
)

echo [OK] Modele disponible
echo.

REM Démarrer Ollama server
echo Demarrage serveur Ollama...
start "Ollama" cmd /k "title Ollama Server && color 0B && ollama serve"
timeout /t 3 /nobreak >nul

REM Démarrer Queue Worker
echo Demarrage Queue Worker...
start "Queue" cmd /k "title Queue Worker && color 0E && cd /d C:\laragon\www\compta && php artisan queue:work --queue=documents"
timeout /t 2 /nobreak >nul

echo.
echo ========================================
echo Services demarres!
echo ========================================
echo.
echo Ouverture du scanner...
start http://localhost/scanner

timeout /t 2 /nobreak >nul

start http://localhost/ocr/analytics

echo.
echo ========================================
echo PRET!
echo ========================================
echo.
echo Scanner: http://localhost/scanner
echo Analytics: http://localhost/ocr/analytics
echo.
echo NE fermez pas les fenetres Ollama et Queue!
echo.
pause
