<?php

namespace App\Services\Payment\Providers;

use App\Contracts\PaymentProviderInterface;
use App\Models\Company;
use App\Models\PaymentTransaction;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

class StripeProvider implements PaymentProviderInterface
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $apiKey = config('payments.providers.stripe.api_key');

        if (empty($apiKey)) {
            throw new \Exception('Stripe API key not configured');
        }

        $this->stripe = new StripeClient($apiKey);
    }

    /**
     * Create a payment session for subscription
     */
    public function createPayment(Subscription $subscription, array $options = []): array
    {
        try {
            $company = $subscription->company;
            $plan = $subscription->plan;

            // Create checkout session
            $session = $this->stripe->checkout->sessions->create([
                'payment_method_types' => ['card', 'sepa_debit', 'bancontact'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => "Abonnement {$plan->name}",
                            'description' => $plan->description ?? '',
                        ],
                        'unit_amount' => (int) ($subscription->amount * 100), // Stripe uses cents
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $options['success_url'] ?? config('payments.urls.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $options['cancel_url'] ?? config('payments.urls.cancel'),
                'client_reference_id' => $subscription->id,
                'customer_email' => $company->email,
                'metadata' => [
                    'subscription_id' => $subscription->id,
                    'company_id' => $company->id,
                    'plan_id' => $plan->id,
                ],
            ]);

            // Log transaction
            PaymentTransaction::logPayment([
                'company_id' => $company->id,
                'subscription_id' => $subscription->id,
                'provider' => 'stripe',
                'provider_payment_id' => $session->id,
                'amount' => $subscription->amount,
                'description' => "Abonnement {$plan->name}",
                'status' => 'pending',
            ]);

            return [
                'checkout_url' => $session->url,
                'payment_id' => $session->id,
                'status' => 'pending',
            ];

        } catch (ApiErrorException $e) {
            Log::error('Stripe payment creation failed', [
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
            // Create or get customer
            $customerId = $this->createCustomer($company);

            $planConfig = config("payments.plans.{$planId}");
            if (!$planConfig) {
                throw new \Exception("Plan {$planId} not found");
            }

            // Get or create price for this plan
            $priceId = $planConfig['stripe_plan_id'] ?? $this->createPrice($planConfig);

            // Create subscription
            $subscription = $this->stripe->subscriptions->create([
                'customer' => $customerId,
                'items' => [['price' => $priceId]],
                'payment_behavior' => 'default_incomplete',
                'payment_settings' => [
                    'save_default_payment_method' => 'on_subscription',
                ],
                'expand' => ['latest_invoice.payment_intent'],
                'metadata' => [
                    'company_id' => $company->id,
                    'plan_id' => $planId,
                ],
            ]);

            return [
                'subscription_id' => $subscription->id,
                'customer_id' => $customerId,
                'status' => $subscription->status,
                'client_secret' => $subscription->latest_invoice->payment_intent->client_secret ?? null,
                'next_payment_date' => $subscription->current_period_end ? date('Y-m-d', $subscription->current_period_end) : null,
            ];

        } catch (ApiErrorException $e) {
            Log::error('Stripe subscription creation failed', [
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
            $this->stripe->subscriptions->cancel($subscriptionId);

            return true;

        } catch (ApiErrorException $e) {
            Log::error('Stripe subscription cancellation failed', [
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
            // Check if it's a checkout session or payment intent
            if (str_starts_with($paymentId, 'cs_')) {
                $session = $this->stripe->checkout->sessions->retrieve($paymentId);

                return [
                    'status' => $session->payment_status,
                    'amount' => $session->amount_total / 100,
                    'currency' => strtoupper($session->currency ?? 'EUR'),
                ];
            }

            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentId);

            return [
                'status' => $paymentIntent->status,
                'amount' => $paymentIntent->amount / 100,
                'currency' => strtoupper($paymentIntent->currency),
            ];

        } catch (ApiErrorException $e) {
            Log::error('Stripe get payment status failed', [
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
            $subscription = $this->stripe->subscriptions->retrieve($subscriptionId);

            return [
                'status' => $subscription->status,
                'next_payment_date' => $subscription->current_period_end ? date('Y-m-d', $subscription->current_period_end) : null,
            ];

        } catch (ApiErrorException $e) {
            Log::error('Stripe get subscription status failed', [
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
     * Create a customer in Stripe
     */
    public function createCustomer(Company $company): string
    {
        try {
            // Check if customer already exists
            if ($company->stripe_customer_id) {
                return $company->stripe_customer_id;
            }

            $customer = $this->stripe->customers->create([
                'name' => $company->name,
                'email' => $company->email,
                'metadata' => [
                    'company_id' => $company->id,
                    'vat_number' => $company->vat_number,
                ],
                'address' => [
                    'line1' => $company->address,
                    'city' => $company->city,
                    'postal_code' => $company->zip_code,
                    'country' => $company->country ?? 'BE',
                ],
            ]);

            // Save customer ID to company
            $company->update(['stripe_customer_id' => $customer->id]);

            return $customer->id;

        } catch (ApiErrorException $e) {
            Log::error('Stripe customer creation failed', [
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
        try {
            $webhookSecret = config('payments.providers.stripe.webhook_secret');

            if (empty($webhookSecret)) {
                Log::warning('Stripe webhook secret not configured');
                return false;
            }

            \Stripe\Webhook::constructEvent(
                json_encode($payload),
                $signature,
                $webhookSecret
            );

            return true;

        } catch (\Exception $e) {
            Log::error('Stripe webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Handle webhook event
     */
    public function handleWebhook(array $payload): array
    {
        try {
            $event = $payload;
            $eventType = $event['type'] ?? null;

            switch ($eventType) {
                case 'checkout.session.completed':
                    return $this->handleCheckoutCompleted($event['data']['object']);

                case 'payment_intent.succeeded':
                    return $this->handlePaymentSucceeded($event['data']['object']);

                case 'payment_intent.payment_failed':
                    return $this->handlePaymentFailed($event['data']['object']);

                case 'invoice.payment_succeeded':
                    return $this->handleInvoicePaymentSucceeded($event['data']['object']);

                case 'customer.subscription.deleted':
                    return $this->handleSubscriptionDeleted($event['data']['object']);

                default:
                    Log::info('Unhandled Stripe webhook event', ['type' => $eventType]);

                    return [
                        'type' => 'unhandled',
                        'data' => ['event_type' => $eventType],
                    ];
            }

        } catch (\Exception $e) {
            Log::error('Stripe webhook handling failed', [
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle checkout session completed
     */
    protected function handleCheckoutCompleted(array $session): array
    {
        $subscriptionId = $session['metadata']['subscription_id'] ?? null;

        if (!$subscriptionId) {
            return ['type' => 'checkout.completed', 'data' => []];
        }

        $transaction = PaymentTransaction::where('provider_payment_id', $session['id'])->first();

        if ($transaction) {
            $transaction->markAsPaid();

            if ($transaction->subscription) {
                $transaction->subscription->update([
                    'status' => 'active',
                    'current_period_start' => now(),
                    'current_period_end' => now()->addMonth(),
                ]);
            }
        }

        return [
            'type' => 'checkout.completed',
            'data' => ['session_id' => $session['id']],
        ];
    }

    /**
     * Handle payment succeeded
     */
    protected function handlePaymentSucceeded(array $paymentIntent): array
    {
        $transaction = PaymentTransaction::where('provider_payment_id', $paymentIntent['id'])->first();

        if ($transaction) {
            $transaction->markAsPaid();
        }

        return [
            'type' => 'payment.succeeded',
            'data' => ['payment_intent_id' => $paymentIntent['id']],
        ];
    }

    /**
     * Handle payment failed
     */
    protected function handlePaymentFailed(array $paymentIntent): array
    {
        $transaction = PaymentTransaction::where('provider_payment_id', $paymentIntent['id'])->first();

        if ($transaction) {
            $transaction->markAsFailed(
                $paymentIntent['last_payment_error']['code'] ?? 'unknown',
                $paymentIntent['last_payment_error']['message'] ?? 'Payment failed'
            );

            if ($transaction->subscription) {
                $transaction->subscription->update(['status' => 'past_due']);
            }
        }

        return [
            'type' => 'payment.failed',
            'data' => ['payment_intent_id' => $paymentIntent['id']],
        ];
    }

    /**
     * Handle invoice payment succeeded (for subscriptions)
     */
    protected function handleInvoicePaymentSucceeded(array $invoice): array
    {
        $subscriptionId = $invoice['subscription'] ?? null;

        if ($subscriptionId) {
            $subscription = \App\Models\Subscription::where('provider_subscription_id', $subscriptionId)->first();

            if ($subscription) {
                $subscription->update([
                    'status' => 'active',
                    'next_payment_date' => date('Y-m-d', $invoice['period_end']),
                ]);

                // Log transaction
                PaymentTransaction::logPayment([
                    'company_id' => $subscription->company_id,
                    'subscription_id' => $subscription->id,
                    'provider' => 'stripe',
                    'provider_payment_id' => $invoice['payment_intent'],
                    'amount' => $invoice['amount_paid'] / 100,
                    'status' => 'paid',
                    'paid_at' => now(),
                    'description' => 'Recurring subscription payment',
                ]);
            }
        }

        return [
            'type' => 'invoice.paid',
            'data' => ['invoice_id' => $invoice['id']],
        ];
    }

    /**
     * Handle subscription deleted
     */
    protected function handleSubscriptionDeleted(array $stripeSubscription): array
    {
        $subscription = \App\Models\Subscription::where('provider_subscription_id', $stripeSubscription['id'])->first();

        if ($subscription) {
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);
        }

        return [
            'type' => 'subscription.cancelled',
            'data' => ['subscription_id' => $stripeSubscription['id']],
        ];
    }

    /**
     * Get provider name
     */
    public function getName(): string
    {
        return 'stripe';
    }

    /**
     * Refund a payment
     */
    public function refund(string $paymentId, ?float $amount = null): array
    {
        try {
            $refundData = ['payment_intent' => $paymentId];

            if ($amount !== null) {
                $refundData['amount'] = (int) ($amount * 100); // Convert to cents
            }

            $refund = $this->stripe->refunds->create($refundData);

            // Log refund transaction
            $transaction = PaymentTransaction::where('provider_payment_id', $paymentId)->first();

            if ($transaction) {
                PaymentTransaction::logRefund([
                    'company_id' => $transaction->company_id,
                    'subscription_id' => $transaction->subscription_id,
                    'provider' => 'stripe',
                    'provider_payment_id' => $paymentId,
                    'provider_refund_id' => $refund->id,
                    'amount' => $refund->amount / 100,
                    'description' => 'Refund for payment ' . $paymentId,
                ]);
            }

            return [
                'refund_id' => $refund->id,
                'status' => $refund->status,
                'amount' => $refund->amount / 100,
            ];

        } catch (ApiErrorException $e) {
            Log::error('Stripe refund failed', [
                'payment_id' => $paymentId,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to refund payment: ' . $e->getMessage());
        }
    }

    /**
     * Create a price for a plan
     */
    protected function createPrice(array $planConfig): string
    {
        try {
            $price = $this->stripe->prices->create([
                'unit_amount' => (int) ($planConfig['price'] * 100),
                'currency' => 'eur',
                'recurring' => ['interval' => $planConfig['interval'] ?? 'month'],
                'product_data' => [
                    'name' => $planConfig['name'],
                ],
            ]);

            return $price->id;

        } catch (ApiErrorException $e) {
            throw new \Exception('Failed to create price: ' . $e->getMessage());
        }
    }
}
