<?php

/**
 * Script de test Ollama pour ComptaBE
 *
 * Usage: php test_ollama.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n=== Test Ollama pour ComptaBE ===\n\n";

// 1. Vérifier configuration
echo "📋 Configuration:\n";
echo "   Provider: " . config('ai.default_provider') . "\n";
echo "   Ollama URL: " . config('ai.ollama.base_url') . "\n";
echo "   Modèle: " . config('ai.ollama.model') . "\n\n";

// 2. Vérifier disponibilité providers
echo "🔍 Vérification des providers disponibles...\n";

try {
    $factory = new \App\Services\AI\AIServiceFactory();
    $providers = $factory->getAvailableProviders();

    foreach ($providers as $name => $info) {
        $status = $info['available'] ? '✅ Disponible' : '❌ Non disponible';
        $cost = $info['cost'] ?? 'N/A';
        echo "   {$name}: {$status} (Coût: {$cost})\n";

        if (!$info['available'] && isset($info['error'])) {
            echo "      Erreur: {$info['error']}\n";
        }
    }
    echo "\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n\n";
    exit(1);
}

// 3. Test Ollama
$ollamaAvailable = $providers['ollama']['available'] ?? false;

if (!$ollamaAvailable) {
    echo "⚠️  Ollama n'est pas disponible!\n\n";
    echo "📝 Instructions d'installation:\n";
    echo "   1. Téléchargez Ollama: https://ollama.com/download\n";
    echo "   2. Installez et démarrez Ollama\n";
    echo "   3. Téléchargez un modèle: ollama pull llama3.1\n";
    echo "   4. Relancez ce script\n\n";
    exit(1);
}

echo "✅ Ollama est disponible!\n\n";

// 4. Lister les modèles installés
echo "📦 Modèles Ollama installés:\n";

try {
    $ollama = new \App\Services\AI\Chat\OllamaAIService();
    $models = $ollama->listModels();

    if (empty($models)) {
        echo "   ⚠️  Aucun modèle installé!\n";
        echo "   Téléchargez un modèle: ollama pull llama3.1\n\n";
        exit(1);
    }

    foreach ($models as $model) {
        $name = $model['name'] ?? 'Unknown';
        $size = isset($model['size']) ? round($model['size'] / 1024 / 1024 / 1024, 1) . ' GB' : 'N/A';
        echo "   - {$name} ({$size})\n";
    }
    echo "\n";

} catch (Exception $e) {
    echo "   ⚠️  Impossible de lister les modèles: " . $e->getMessage() . "\n\n";
}

// 5. Test simple de message
echo "💬 Test d'envoi de message à Ollama...\n";

try {
    $service = \App\Services\AI\AIServiceFactory::make('ollama');

    $messages = [
        ['role' => 'user', 'content' => 'Bonjour! Réponds juste "OK" pour confirmer que tu fonctionnes.']
    ];

    echo "   Envoi du message...\n";
    $startTime = microtime(true);

    $response = $service->sendMessage($messages);

    $duration = round(microtime(true) - $startTime, 2);
    $text = $service->extractTextContent($response);

    echo "   ✅ Réponse reçue en {$duration}s\n";
    echo "   Réponse: " . substr($text, 0, 100) . (strlen($text) > 100 ? '...' : '') . "\n\n";

    // Afficher les tokens
    if (isset($response['usage'])) {
        $input = $response['usage']['input_tokens'] ?? 0;
        $output = $response['usage']['output_tokens'] ?? 0;
        echo "   📊 Tokens: {$input} input, {$output} output\n";
        echo "   💰 Coût: $0.00 (GRATUIT!)\n\n";
    }

} catch (Exception $e) {
    echo "   ❌ Erreur lors du test: " . $e->getMessage() . "\n\n";
    exit(1);
}

// 6. Résumé final
echo "═══════════════════════════════════════\n";
echo "✅ SUCCÈS - Ollama fonctionne correctement!\n";
echo "═══════════════════════════════════════\n\n";

echo "📋 Prochaines étapes:\n";
echo "   1. Connectez-vous à ComptaBE\n";
echo "   2. Cliquez sur l'icône chat en bas à droite\n";
echo "   3. Testez avec: 'Montre-moi mes factures impayées'\n";
echo "   4. Profitez de l'AI GRATUIT! 🎉\n\n";

echo "💡 Astuce: Pour changer de modèle, modifiez OLLAMA_MODEL dans .env\n";
echo "   Modèles recommandés: llama3.1, mistral, phi3, qwen2.5\n\n";
