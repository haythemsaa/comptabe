<?php

namespace App\Contracts;

use App\Models\Company;
use App\Models\Subscription;

interface PaymentProviderInterface
{
    /**
     * Create a payment session for subscription
     *
     * @param Subscription $subscription
     * @param array $options Additional options (success_url, cancel_url, etc.)
     * @return array ['checkout_url' => string, 'payment_id' => string]
     */
    public function createPayment(Subscription $subscription, array $options = []): array;

    /**
     * Create a recurring subscription (monthly billing)
     *
     * @param Company $company
     * @param string $planId Plan identifier
     * @param array $options Additional options
     * @return array ['subscription_id' => string, 'status' => string]
     */
    public function createSubscription(Company $company, string $planId, array $options = []): array;

    /**
     * Cancel a recurring subscription
     *
     * @param string $subscriptionId Provider's subscription ID
     * @return bool
     */
    public function cancelSubscription(string $subscriptionId): bool;

    /**
     * Get payment status
     *
     * @param string $paymentId Provider's payment ID
     * @return array ['status' => string, 'amount' => float, 'currency' => string]
     */
    public function getPaymentStatus(string $paymentId): array;

    /**
     * Get subscription status
     *
     * @param string $subscriptionId Provider's subscription ID
     * @return array ['status' => string, 'next_payment_date' => ?string]
     */
    public function getSubscriptionStatus(string $subscriptionId): array;

    /**
     * Create a customer in provider's system
     *
     * @param Company $company
     * @return string Customer ID
     */
    public function createCustomer(Company $company): string;

    /**
     * Verify webhook signature
     *
     * @param array $payload Webhook payload
     * @param string $signature Signature header
     * @return bool
     */
    public function verifyWebhookSignature(array $payload, string $signature): bool;

    /**
     * Handle webhook event
     *
     * @param array $payload Webhook payload
     * @return array ['type' => string, 'data' => array]
     */
    public function handleWebhook(array $payload): array;

    /**
     * Get provider name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Refund a payment
     *
     * @param string $paymentId Provider's payment ID
     * @param float $amount Amount to refund (null = full refund)
     * @return array ['refund_id' => string, 'status' => string]
     */
    public function refund(string $paymentId, ?float $amount = null): array;
}
