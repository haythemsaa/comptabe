<?php

namespace App\Http\Controllers;

use App\Services\Payment\PaymentProviderFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle Mollie webhook
     */
    public function mollie(Request $request)
    {
        try {
            Log::info('Mollie webhook received', $request->all());

            $provider = PaymentProviderFactory::make('mollie');

            // Mollie sends payment ID in the request
            $paymentId = $request->input('id');

            if (!$paymentId) {
                Log::warning('Mollie webhook: no payment ID');
                return response()->json(['error' => 'No payment ID'], 400);
            }

            // Handle the webhook
            $result = $provider->handleWebhook(['id' => $paymentId]);

            // Process based on event type
            $this->processWebhookResult($result);

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            Log::error('Mollie webhook error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Handle Stripe webhook
     */
    public function stripe(Request $request)
    {
        try {
            $signature = $request->header('Stripe-Signature');

            Log::info('Stripe webhook received', [
                'type' => $request->input('type'),
                'has_signature' => !empty($signature),
            ]);

            $provider = PaymentProviderFactory::make('stripe');

            // Verify webhook signature
            if (!$provider->verifyWebhookSignature($request->all(), $signature)) {
                Log::warning('Stripe webhook: invalid signature');
                return response()->json(['error' => 'Invalid signature'], 403);
            }

            // Handle the webhook
            $result = $provider->handleWebhook($request->all());

            // Process based on event type
            $this->processWebhookResult($result);

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            Log::error('Stripe webhook error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Process webhook result and take actions
     */
    protected function processWebhookResult(array $result): void
    {
        $type = $result['type'];
        $data = $result['data'];

        match($type) {
            'payment.paid' => $this->handlePaymentPaid($data),
            'payment.failed' => $this->handlePaymentFailed($data),
            'payment.pending' => $this->handlePaymentPending($data),
            'checkout.completed' => $this->handleCheckoutCompleted($data),
            'invoice.paid' => $this->handleInvoicePaid($data),
            'subscription.cancelled' => $this->handleSubscriptionCancelled($data),
            default => Log::info('Unhandled webhook type', ['type' => $type, 'data' => $data]),
        };
    }

    /**
     * Handle successful payment
     */
    protected function handlePaymentPaid(array $data): void
    {
        Log::info('Payment paid', $data);

        // You can add custom logic here, e.g.:
        // - Send confirmation email
        // - Update subscription status
        // - Trigger analytics event
        // - etc.
    }

    /**
     * Handle failed payment
     */
    protected function handlePaymentFailed(array $data): void
    {
        Log::warning('Payment failed', $data);

        // Custom logic:
        // - Send failure notification email
        // - Alert admin if critical
        // - Suspend subscription if multiple failures
        // - etc.
    }

    /**
     * Handle pending payment
     */
    protected function handlePaymentPending(array $data): void
    {
        Log::info('Payment pending', $data);

        // Custom logic if needed
    }

    /**
     * Handle checkout completed
     */
    protected function handleCheckoutCompleted(array $data): void
    {
        Log::info('Checkout completed', $data);

        // Custom logic:
        // - Send welcome email
        // - Trigger onboarding flow
        // - etc.
    }

    /**
     * Handle invoice paid (recurring subscription)
     */
    protected function handleInvoicePaid(array $data): void
    {
        Log::info('Invoice paid', $data);

        // Custom logic:
        // - Send receipt email
        // - Update usage stats
        // - etc.
    }

    /**
     * Handle subscription cancelled
     */
    protected function handleSubscriptionCancelled(array $data): void
    {
        Log::warning('Subscription cancelled', $data);

        // Custom logic:
        // - Send cancellation confirmation email
        // - Ask for feedback
        // - Offer win-back discount
        // - etc.
    }
}
