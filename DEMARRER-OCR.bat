@echo off
title ComptaBE - Demarrage OCR System
color 0A

echo.
echo  ========================================
echo  ComptaBE - Systeme OCR ^& IA
echo  ========================================
echo.

REM Vérifier si Ollama est installé
where ollama >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    color 0C
    echo  [ERREUR] Ollama n'est pas installe!
    echo.
    echo  Veuillez d'abord executer: INSTALLER-OLLAMA.bat
    echo.
    pause
    exit /b 1
)

echo  [OK] Ollama est installe
echo.

REM Vérifier si le modèle llama3.1 existe
ollama list | findstr "llama3.1" >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    color 0E
    echo  [ATTENTION] Modele llama3.1 non trouve!
    echo.
    echo  Telechargement du modele (environ 2.5 GB)...
    echo  Ceci peut prendre 5-15 minutes.
    echo.
    ollama pull llama3.1

    if %ERRORLEVEL% NEQ 0 (
        color 0C
        echo.
        echo  [ERREUR] Echec du telechargement du modele
        echo.
        pause
        exit /b 1
    )
)

echo  [OK] Modele llama3.1 disponible
echo.

REM Vérifier si Ollama server tourne déjà
curl -s http://localhost:11434/api/tags >nul 2>nul
if %ERRORLEVEL% EQU 0 (
    echo  [OK] Ollama server deja en cours d'execution
    echo.
) else (
    echo  [INFO] Demarrage du serveur Ollama...
    echo.

    REM Démarrer Ollama dans une nouvelle fenêtre
    start "Ollama Server" cmd /k "title Ollama Server && color 0B && echo Serveur Ollama en cours... && echo. && echo Ne fermez pas cette fenetre! && echo. && ollama serve"

    REM Attendre que le serveur démarre
    echo  Attente du demarrage du serveur Ollama...
    timeout /t 3 /nobreak >nul

    :wait_ollama
    curl -s http://localhost:11434/api/tags >nul 2>nul
    if %ERRORLEVEL% NEQ 0 (
        echo  En attente...
        timeout /t 2 /nobreak >nul
        goto wait_ollama
    )

    echo  [OK] Serveur Ollama demarre!
    echo.
)

REM Vérifier si on est dans le bon répertoire
if not exist "artisan" (
    echo  [ERREUR] Fichier 'artisan' introuvable!
    echo  Assurez-vous d'etre dans le repertoire C:\laragon\www\compta
    echo.
    pause
    exit /b 1
)

echo  [INFO] Demarrage du Queue Worker Laravel...
echo.

REM Démarrer le queue worker dans une nouvelle fenêtre
start "Laravel Queue Worker" cmd /k "title Laravel Queue Worker && color 0E && echo Queue Worker Laravel en cours... && echo. && echo Ne fermez pas cette fenetre! && echo. && php artisan queue:work --queue=documents --timeout=300 --tries=3"

echo  [OK] Queue Worker demarre!
echo.

REM Attendre un peu
timeout /t 2 /nobreak >nul

echo  ========================================
echo  Tous les services sont demarres!
echo  ========================================
echo.
echo  Services en cours d'execution:
echo  - Ollama Server (port 11434)
echo  - Laravel Queue Worker
echo.
echo  ========================================
echo  Ouverture du Scanner dans le navigateur...
echo  ========================================
echo.

REM Ouvrir le scanner
start http://localhost/scanner

REM Attendre 2 secondes
timeout /t 2 /nobreak >nul

REM Ouvrir le dashboard analytics
start http://localhost/ocr/analytics

echo.
echo  Pages ouvertes:
echo  - Scanner OCR: http://localhost/scanner
echo  - Dashboard Analytics: http://localhost/ocr/analytics
echo.
echo  ========================================
echo  Pret pour les tests!
echo  ========================================
echo.
echo  Pour arreter les services:
echo  - Fermez les fenetres "Ollama Server" et "Queue Worker"
echo  - Ou appuyez sur Ctrl+C dans chaque fenetre
echo.
echo  Logs Laravel: storage\logs\laravel.log
echo.
echo  ========================================
echo.

pause
