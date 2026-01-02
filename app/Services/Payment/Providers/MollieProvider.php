<?php

namespace App\Services\Payment\Providers;

use App\Contracts\PaymentProviderInterface;
use App\Models\Company;
use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use App\Models\Subscription;
use Mollie\Laravel\Facades\Mollie;
use Illuminate\Support\Facades\Log;

class MollieProvider implements PaymentProviderInterface
{
    protected $client;

    public function __construct()
    {
        $apiKey = config('payments.providers.mollie.api_key');

        if (empty($apiKey)) {
            throw new \Exception('Mollie API key not configured');
        }

        Mollie::api()->setApiKey($apiKey);
        $this->client = Mollie::api();
    }

    /**
     * Create a payment session for subscription
     */
    public function createPayment(Subscription $subscription, array $options = []): array
    {
        try {
            $company = $subscription->company;
            $plan = $subscription->plan;

            // Create payment with Mollie
            $payment = $this->client->payments->create([
                'amount' => [
                    'currency' => 'EUR',
                    'value' => number_format($subscription->amount, 2, '.', ''),
                ],
                'description' => $options['description'] ?? "Abonnement {$plan->name} - {$company->name}",
                'redirectUrl' => $options['success_url'] ?? config('payments.urls.success'),
                'webhookUrl' => config('payments.urls.webhook_mollie'),
                'metadata' => [
                    'subscription_id' => $subscription->id,
                    'company_id' => $company->id,
                    'plan_id' => $plan->id,
                ],
                'locale' => $options['locale'] ?? config('payments.locale', 'fr_BE'),
            ]);

            // Log transaction
            PaymentTransaction::logPayment([
                'company_id' => $company->id,
                'subscription_id' => $subscription->id,
                'provider' => 'mollie',
                'provider_payment_id' => $payment->id,
                'amount' => $subscription->amount,
                'description' => $payment->description,
                'status' => $payment->status,
            ]);

            return [
                'checkout_url' => $payment->getCheckoutUrl(),
                'payment_id' => $payment->id,
                'status' => $payment->status,
            ];

        } catch (\Exception $e) {
            Log::error('Mollie payment creation failed', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to create payment: ' . $e->getMessage());
        }
    }

    /**
     * Create a recurring subscription (monthly billing)
     */
    public function createSubscription(Company $company, string $planId, array $options = []): array
    {
        try {
            // First, create or get customer
            $customerId = $this->createCustomer($company);

            $planConfig = config("payments.plans.{$planId}");
            if (!$planConfig) {
                throw new \Exception("Plan {$planId} not found");
            }

            // Create subscription with Mollie
            $subscription = $this->client->subscriptions->createFor($customerId, [
                'amount' => [
                    'currency' => 'EUR',
                    'value' => number_format($planConfig['price'], 2, '.', ''),
                ],
                'interval' => $planConfig['interval'] ?? 'monthly',
                'description' => "Abonnement {$planConfig['name']} - {$company->name}",
                'webhookUrl' => config('payments.urls.webhook_mollie'),
                'metadata' => [
                    'company_id' => $company->id,
                    'plan_id' => $planId,
                ],
            ]);

            return [
                'subscription_id' => $subscription->id,
                'customer_id' => $customerId,
                'status' => $subscription->status,
                'next_payment_date' => $subscription->nextPaymentDate ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('Mollie subscription creation failed', [
                'company_id' => $company->id,
                'plan_id' => $planId,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to create subscription: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a recurring subscription
     */
    public function cancelSubscription(string $subscriptionId): bool
    {
        try {
            // Get subscription to find customer ID
            $localSubscription = \App\Models\Subscription::where('provider_subscription_id', $subscriptionId)->first();

            if (!$localSubscription || !$localSubscription->provider_customer_id) {
                throw new \Exception('Subscription not found or customer ID missing');
            }

            $this->client->subscriptions->cancelFor(
                $localSubscription->provider_customer_id,
                $subscriptionId
            );

            return true;

        } catch (\Exception $e) {
            Log::error('Mollie subscription cancellation failed', [
                'subscription_id' => $subscriptionId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $paymentId): array
    {
        try {
            $payment = $this->client->payments->get($paymentId);

            return [
                'status' => $payment->status,
                'amount' => (float) $payment->amount->value,
                'currency' => $payment->amount->currency,
                'paid_at' => $payment->paidAt ? $payment->paidAt->format('Y-m-d H:i:s') : null,
                'method' => $payment->method ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('Mollie get payment status failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'failed',
                'amount' => 0,
                'currency' => 'EUR',
            ];
        }
    }

    /**
     * Get subscription status
     */
    public function getSubscriptionStatus(string $subscriptionId): array
    {
        try {
            $localSubscription = \App\Models\Subscription::where('provider_subscription_id', $subscriptionId)->first();

            if (!$localSubscription) {
                throw new \Exception('Subscription not found');
            }

            $subscription = $this->client->subscriptions->getFor(
                $localSubscription->provider_customer_id,
                $subscriptionId
            );

            return [
                'status' => $subscription->status,
                'next_payment_date' => $subscription->nextPaymentDate ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('Mollie get subscription status failed', [
                'subscription_id' => $subscriptionId,
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'unknown',
                'next_payment_date' => null,
            ];
        }
    }

    /**
     * Create a customer in Mollie
     */
    public function createCustomer(Company $company): string
    {
        try {
            // Check if customer already exists
            if ($company->mollie_customer_id) {
                return $company->mollie_customer_id;
            }

            $customer = $this->client->customers->create([
                'name' => $company->name,
                'email' => $company->email,
                'locale' => config('payments.locale', 'fr_BE'),
                'metadata' => [
                    'company_id' => $company->id,
                    'vat_number' => $company->vat_number,
                ],
            ]);

            // Save customer ID to company
            $company->update(['mollie_customer_id' => $customer->id]);

            return $customer->id;

        } catch (\Exception $e) {
            Log::error('Mollie customer creation failed', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to create customer: ' . $e->getMessage());
        }
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(array $payload, string $signature): bool
    {
        // Mollie doesn't use signature verification
        // Instead, we fetch the payment/subscription from their API to verify
        return true;
    }

    /**
     * Handle webhook event
     */
    public function handleWebhook(array $payload): array
    {
        try {
            $paymentId = $payload['id'] ?? null;

            if (!$paymentId) {
                throw new \Exception('Payment ID not found in webhook payload');
            }

            // Fetch payment from Mollie to verify and get latest status
            $payment = $this->client->payments->get($paymentId);

            // Find transaction in database
            $transaction = PaymentTransaction::where('provider_payment_id', $paymentId)->first();

            if (!$transaction) {
                Log::warning('Mollie webhook: transaction not found', ['payment_id' => $paymentId]);

                return [
                    'type' => 'payment.unknown',
                    'data' => [],
                ];
            }

            // Update transaction status
            if ($payment->isPaid()) {
                $transaction->markAsPaid();

                // Update subscription status
                if ($transaction->subscription_id) {
                    $subscription = $transaction->subscription;
                    $subscription->update([
                        'status' => 'active',
                        'current_period_start' => now(),
                        'current_period_end' => now()->addMonth(),
                    ]);
                }

                return [
                    'type' => 'payment.paid',
                    'data' => [
                        'payment_id' => $payment->id,
                        'amount' => $payment->amount->value,
                        'transaction_id' => $transaction->id,
                    ],
                ];
            }

            if ($payment->isFailed() || $payment->isExpired() || $payment->isCanceled()) {
                $transaction->markAsFailed(
                    $payment->status,
                    $payment->details->failureReason ?? 'Payment failed'
                );

                // Update subscription to past_due
                if ($transaction->subscription_id) {
                    $transaction->subscription->update(['status' => 'past_due']);
                }

                return [
                    'type' => 'payment.failed',
                    'data' => [
                        'payment_id' => $payment->id,
                        'reason' => $payment->details->failureReason ?? 'Unknown',
                    ],
                ];
            }

            return [
                'type' => 'payment.pending',
                'data' => ['payment_id' => $payment->id],
            ];

        } catch (\Exception $e) {
            Log::error('Mollie webhook handling failed', [
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get provider name
     */
    public function getName(): string
    {
        return 'mollie';
    }

    /**
     * Refund a payment
     */
    public function refund(string $paymentId, ?float $amount = null): array
    {
        try {
            $payment = $this->client->payments->get($paymentId);

            $refundData = [];

            if ($amount !== null) {
                $refundData['amount'] = [
                    'currency' => 'EUR',
                    'value' => number_format($amount, 2, '.', ''),
                ];
            }

            $refund = $payment->refund($refundData);

            // Log refund transaction
            $transaction = PaymentTransaction::where('provider_payment_id', $paymentId)->first();

            if ($transaction) {
                PaymentTransaction::logRefund([
                    'company_id' => $transaction->company_id,
                    'subscription_id' => $transaction->subscription_id,
                    'provider' => 'mollie',
                    'provider_payment_id' => $paymentId,
                    'provider_refund_id' => $refund->id,
                    'amount' => $amount ?? (float) $payment->amount->value,
                    'description' => 'Refund for payment ' . $paymentId,
                ]);
            }

            return [
                'refund_id' => $refund->id,
                'status' => $refund->status,
                'amount' => (float) $refund->amount->value,
            ];

        } catch (\Exception $e) {
            Log::error('Mollie refund failed', [
                'payment_id' => $paymentId,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to refund payment: ' . $e->getMessage());
        }
    }
}
