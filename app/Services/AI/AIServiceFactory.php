<?php

namespace App\Services\AI;

use App\Services\AI\Contracts\AIServiceInterface;
use App\Services\AI\Chat\ClaudeAIService;
use App\Services\AI\Chat\OllamaAIService;

/**
 * Factory to create AI service instances based on configuration
 */
class AIServiceFactory
{
    /**
     * Create AI service instance based on configured provider
     *
     * @param string|null $provider Force specific provider (ollama, claude)
     * @return AIServiceInterface
     * @throws \Exception
     */
    public static function make(?string $provider = null): AIServiceInterface
    {
        $provider = $provider ?? config('ai.default_provider', 'ollama');

        return match($provider) {
            'ollama' => new OllamaAIService(),
            'claude' => new ClaudeAIService(),
            default => throw new \Exception("Unsupported AI provider: {$provider}")
        };
    }

    /**
     * Get the default configured provider
     */
    public static function getDefaultProvider(): string
    {
        return config('ai.default_provider', 'ollama');
    }

    /**
     * Check if a provider is available
     */
    public static function isProviderAvailable(string $provider): bool
    {
        try {
            $service = self::make($provider);
            return $service->isAvailable();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get first available provider from list
     *
     * @param array $providers List of providers to try
     * @return string|null
     */
    public static function getFirstAvailableProvider(array $providers = ['ollama', 'claude']): ?string
    {
        foreach ($providers as $provider) {
            if (self::isProviderAvailable($provider)) {
                return $provider;
            }
        }

        return null;
    }

    /**
     * Get list of all configured providers
     */
    public static function getAvailableProviders(): array
    {
        $providers = [];

        foreach (['ollama', 'claude'] as $providerName) {
            try {
                $service = self::make($providerName);
                $providers[$providerName] = [
                    'name' => $providerName,
                    'available' => $service->isAvailable(),
                    'cost' => $providerName === 'ollama' ? 'Free' : 'Paid',
                ];
            } catch (\Exception $e) {
                $providers[$providerName] = [
                    'name' => $providerName,
                    'available' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $providers;
    }
}
