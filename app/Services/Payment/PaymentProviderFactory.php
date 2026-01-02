<?php

namespace App\Services\Payment;

use App\Contracts\PaymentProviderInterface;
use App\Services\Payment\Providers\MollieProvider;
use App\Services\Payment\Providers\StripeProvider;
use InvalidArgumentException;

class PaymentProviderFactory
{
    /**
     * Available providers
     */
    private const PROVIDERS = [
        'mollie' => MollieProvider::class,
        'stripe' => StripeProvider::class,
    ];

    /**
     * Create a payment provider instance
     *
     * @param string|null $provider Provider name (mollie, stripe)
     * @return PaymentProviderInterface
     * @throws InvalidArgumentException
     */
    public static function make(?string $provider = null): PaymentProviderInterface
    {
        // Use default provider from config if not specified
        $provider = $provider ?? config('payments.default_provider', 'mollie');

        if (!isset(self::PROVIDERS[$provider])) {
            throw new InvalidArgumentException("Payment provider [{$provider}] is not supported.");
        }

        $providerClass = self::PROVIDERS[$provider];

        return app($providerClass);
    }

    /**
     * Get all available providers
     *
     * @return array
     */
    public static function getAvailableProviders(): array
    {
        return array_keys(self::PROVIDERS);
    }

    /**
     * Check if a provider is available
     *
     * @param string $provider
     * @return bool
     */
    public static function isProviderAvailable(string $provider): bool
    {
        return isset(self::PROVIDERS[$provider]);
    }

    /**
     * Get provider instance by name with validation
     *
     * @param string $provider
     * @return PaymentProviderInterface
     */
    public static function getProvider(string $provider): PaymentProviderInterface
    {
        if (!self::isProviderAvailable($provider)) {
            throw new InvalidArgumentException("Payment provider [{$provider}] is not configured.");
        }

        // Check if API keys are configured
        $apiKey = config("payments.providers.{$provider}.api_key");
        if (empty($apiKey)) {
            throw new InvalidArgumentException("Payment provider [{$provider}] is not configured. Missing API key.");
        }

        return self::make($provider);
    }
}
