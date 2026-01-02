# Installation Automatique Ollama pour ComptaBE
# Exécuter en tant qu'administrateur

Write-Host "============================================" -ForegroundColor Cyan
Write-Host "Installation Ollama pour ComptaBE" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

# Vérifier si Ollama est déjà installé
Write-Host "[1/4] Vérification si Ollama est déjà installé..." -ForegroundColor Yellow

$ollamaPath = Get-Command ollama -ErrorAction SilentlyContinue

if ($ollamaPath) {
    Write-Host "✓ Ollama est déjà installé!" -ForegroundColor Green
    Write-Host "Version: " -NoNewline
    ollama --version
    Write-Host ""

    $response = Read-Host "Voulez-vous réinstaller? (o/N)"
    if ($response -ne 'o' -and $response -ne 'O') {
        Write-Host "Installation annulée." -ForegroundColor Yellow
        exit 0
    }
}

# Télécharger Ollama
Write-Host "[2/4] Téléchargement d'Ollama..." -ForegroundColor Yellow

$ollamaUrl = "https://ollama.com/download/OllamaSetup.exe"
$installerPath = "$env:TEMP\OllamaSetup.exe"

try {
    Write-Host "Téléchargement depuis $ollamaUrl..." -ForegroundColor Gray
    Invoke-WebRequest -Uri $ollamaUrl -OutFile $installerPath -UseBasicParsing
    Write-Host "✓ Téléchargement terminé!" -ForegroundColor Green
} catch {
    Write-Host "✗ Erreur lors du téléchargement: $_" -ForegroundColor Red
    Write-Host ""
    Write-Host "Veuillez télécharger manuellement depuis: https://ollama.ai/download" -ForegroundColor Yellow
    pause
    exit 1
}

# Installer Ollama
Write-Host ""
Write-Host "[3/4] Installation d'Ollama..." -ForegroundColor Yellow
Write-Host "ATTENTION: L'installateur va s'ouvrir. Suivez les étapes." -ForegroundColor Cyan
Write-Host ""

try {
    Start-Process -FilePath $installerPath -Wait
    Write-Host "✓ Installation terminée!" -ForegroundColor Green
} catch {
    Write-Host "✗ Erreur lors de l'installation: $_" -ForegroundColor Red
    pause
    exit 1
}

# Nettoyer le fichier temporaire
Remove-Item $installerPath -ErrorAction SilentlyContinue

# Vérifier l'installation
Write-Host ""
Write-Host "[4/4] Vérification de l'installation..." -ForegroundColor Yellow

Start-Sleep -Seconds 2

# Refresh PATH
$env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")

$ollamaPath = Get-Command ollama -ErrorAction SilentlyContinue

if ($ollamaPath) {
    Write-Host "✓ Ollama installé avec succès!" -ForegroundColor Green
    Write-Host ""
    ollama --version
    Write-Host ""
} else {
    Write-Host "✗ Ollama n'a pas été trouvé dans le PATH." -ForegroundColor Red
    Write-Host "Veuillez redémarrer votre terminal ou votre ordinateur." -ForegroundColor Yellow
    pause
    exit 1
}

# Télécharger le modèle llama3.1
Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "Téléchargement du modèle llama3.1" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "ATTENTION: Ceci va télécharger environ 2.5 GB" -ForegroundColor Yellow
Write-Host "Le téléchargement peut prendre 5-15 minutes selon votre connexion." -ForegroundColor Yellow
Write-Host ""

$response = Read-Host "Continuer le téléchargement maintenant? (O/n)"
if ($response -eq 'n' -or $response -eq 'N') {
    Write-Host ""
    Write-Host "Vous pourrez télécharger le modèle plus tard avec:" -ForegroundColor Yellow
    Write-Host "ollama pull llama3.1" -ForegroundColor Cyan
    Write-Host ""
    pause
    exit 0
}

Write-Host ""
Write-Host "Téléchargement du modèle llama3.1..." -ForegroundColor Yellow
Write-Host "Veuillez patienter..." -ForegroundColor Gray
Write-Host ""

try {
    ollama pull llama3.1

    Write-Host ""
    Write-Host "✓ Modèle llama3.1 téléchargé avec succès!" -ForegroundColor Green
    Write-Host ""

    # Afficher les modèles installés
    Write-Host "Modèles installés:" -ForegroundColor Cyan
    ollama list

} catch {
    Write-Host "✗ Erreur lors du téléchargement du modèle: $_" -ForegroundColor Red
    Write-Host ""
    Write-Host "Vous pouvez réessayer manuellement avec:" -ForegroundColor Yellow
    Write-Host "ollama pull llama3.1" -ForegroundColor Cyan
}

# Instructions finales
Write-Host ""
Write-Host "============================================" -ForegroundColor Green
Write-Host "Installation terminée!" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Green
Write-Host ""
Write-Host "PROCHAINES ÉTAPES:" -ForegroundColor Cyan
Write-Host ""
Write-Host "1. Ouvrez un nouveau terminal et lancez:" -ForegroundColor White
Write-Host "   ollama serve" -ForegroundColor Yellow
Write-Host ""
Write-Host "2. Dans un autre terminal, lancez:" -ForegroundColor White
Write-Host "   cd C:\laragon\www\compta" -ForegroundColor Yellow
Write-Host "   php artisan queue:work --queue=documents" -ForegroundColor Yellow
Write-Host ""
Write-Host "3. Ouvrez votre navigateur:" -ForegroundColor White
Write-Host "   http://localhost/scanner" -ForegroundColor Yellow
Write-Host ""
Write-Host "Ou utilisez le script:" -ForegroundColor White
Write-Host "   .\ouvrir-scanner.bat" -ForegroundColor Yellow
Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

pause
