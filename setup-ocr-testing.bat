@echo off
echo ============================================
echo Configuration OCR Testing - ComptaBE
echo ============================================
echo.

REM Check if .env exists
if not exist ".env" (
    echo ERREUR: Fichier .env introuvable!
    echo Copiez .env.example vers .env d'abord
    pause
    exit /b 1
)

echo [1/5] Verification Ollama...
echo.

REM Check if Ollama is installed
where ollama >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ATTENTION: Ollama n'est pas installe!
    echo.
    echo Telechargez depuis: https://ollama.ai/download
    echo Installez Ollama et relancez ce script.
    echo.
    pause
    exit /b 1
)

echo Ollama trouve: OK
echo.

echo [2/5] Verification modele llama3.2...
echo.

REM Check if llama3.2 model exists
ollama list | findstr "llama3.2" >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo Modele llama3.2 non trouve. Telechargement...
    echo ATTENTION: Ceci peut prendre 5-10 minutes selon votre connexion
    echo.
    ollama pull llama3.2
    if %ERRORLEVEL% NEQ 0 (
        echo ERREUR: Echec telechargement modele
        pause
        exit /b 1
    )
) else (
    echo Modele llama3.2: OK
)
echo.

echo [3/5] Configuration .env...
echo.

REM Add or update Ollama configuration in .env
findstr /C:"OLLAMA_ENDPOINT" .env >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo OLLAMA_ENDPOINT=http://localhost:11434 >> .env
    echo OLLAMA_MODEL=llama3.2 >> .env
    echo Configuration Ollama ajoutee a .env
) else (
    echo Configuration Ollama deja presente dans .env
)

REM Check Queue configuration
findstr /C:"QUEUE_CONNECTION" .env >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ATTENTION: QUEUE_CONNECTION non configure dans .env
    echo Ajout de QUEUE_CONNECTION=database par defaut
    echo QUEUE_CONNECTION=database >> .env
)

echo.
echo [4/5] Verification des services...
echo.

REM Check if Redis is running (optional)
netstat -an | findstr "6379" >nul 2>nul
if %ERRORLEVEL% EQU 0 (
    echo Redis detecte sur port 6379: OK
) else (
    echo Redis non detecte (optionnel - queue database sera utilisee)
)

echo.
echo [5/5] Instructions finales...
echo.
echo ============================================
echo Configuration terminee!
echo ============================================
echo.
echo PROCHAINES ETAPES:
echo.
echo 1. Demarrer Ollama (si pas deja lance):
echo    Dans un nouveau terminal: ollama serve
echo.
echo 2. Demarrer le queue worker Laravel:
echo    Dans un nouveau terminal: php artisan queue:work --queue=documents
echo.
echo 3. Acceder au scanner:
echo    URL: http://localhost/scanner
echo    ou: http://compta.test/scanner (si domaine virtuel)
echo.
echo 4. Acceder aux analytics OCR:
echo    URL: http://localhost/ocr/analytics
echo.
echo ============================================
echo Fichiers de test recommandes:
echo ============================================
echo.
echo - Factures PDF belges avec numeros TVA BE
echo - Images claires (JPG/PNG) de factures
echo - Evitez les scans flous ou manuscrits pour premiers tests
echo.
echo ============================================
echo Commandes utiles:
echo ============================================
echo.
echo - Verifier Ollama: ollama list
echo - Tester Ollama: ollama run llama3.2 "Bonjour"
echo - Voir logs Laravel: tail -f storage\logs\laravel.log
echo - Voir jobs queue: php artisan queue:failed
echo.
echo ============================================
echo.

pause
