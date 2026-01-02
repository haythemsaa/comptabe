@echo off
title Test Ollama - ComptaBE
color 0B

echo.
echo ========================================
echo Test de l'installation Ollama
echo ========================================
echo.

REM Test 1: Vérifier si Ollama est installé
echo [Test 1/5] Verification installation Ollama...
where ollama >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    color 0C
    echo [ECHEC] Ollama n'est pas installe ou pas dans le PATH
    echo.
    echo Solution: Executez INSTALLER-OLLAMA.bat
    echo.
    pause
    exit /b 1
)
echo [OK] Ollama est installe
echo.

REM Test 2: Vérifier la version
echo [Test 2/5] Version Ollama...
ollama --version
echo.

REM Test 3: Vérifier si le modèle llama3.1 existe
echo [Test 3/5] Verification modele llama3.1...
ollama list | findstr "llama3.1" >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    color 0E
    echo [ATTENTION] Modele llama3.1 non trouve
    echo.
    echo Les modeles installes sont:
    ollama list
    echo.
    echo Pour installer llama3.1, executez:
    echo ollama pull llama3.1
    echo.
    pause
    exit /b 1
)
echo [OK] Modele llama3.1 present
echo.

REM Test 4: Vérifier si le serveur peut démarrer
echo [Test 4/5] Test de connexion serveur Ollama...
curl -s http://localhost:11434/api/tags >nul 2>nul
if %ERRORLEVEL% EQU 0 (
    echo [OK] Serveur Ollama deja en cours
) else (
    echo [INFO] Serveur pas encore demarre (normal)
    echo.
    echo Demarrage du serveur pour test...
    start /min ollama serve
    timeout /t 3 /nobreak >nul

    curl -s http://localhost:11434/api/tags >nul 2>nul
    if %ERRORLEVEL% EQU 0 (
        echo [OK] Serveur Ollama demarre avec succes
    ) else (
        color 0C
        echo [ECHEC] Impossible de demarrer le serveur
        echo.
        pause
        exit /b 1
    )
)
echo.

REM Test 5: Test génération simple
echo [Test 5/5] Test generation IA (peut prendre 10-15s)...
echo.
echo Question test: "Dis bonjour en francais en 3 mots"
echo.

ollama run llama3.1 "Dis bonjour en francais en 3 mots" --verbose=false 2>nul

if %ERRORLEVEL% EQU 0 (
    echo.
    echo.
    color 0A
    echo ========================================
    echo [SUCCES] Tous les tests sont passes!
    echo ========================================
    echo.
    echo Ollama est pret a etre utilise avec ComptaBE
    echo.
    echo Prochaine etape:
    echo - Executez: DEMARRER-OCR.bat
    echo.
) else (
    color 0E
    echo.
    echo [ATTENTION] La generation a echoue
    echo.
    echo Verifiez que le serveur Ollama tourne.
    echo.
)

echo ========================================
echo.
pause
