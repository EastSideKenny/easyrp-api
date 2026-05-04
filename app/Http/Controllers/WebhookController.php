<?php

namespace App\Http\Controllers;

use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;

class WebhookController extends CashierWebhookController
{
    public function __construct(
        protected SubscriptionService $subscriptionService,
    ) {
        parent::__construct();
    }

    /**
     * POST /api/stripe/webhook
     *
     * Uses Cashier's handler so `customer.subscription.*` updates match Stripe.
     * Extra logging only (Dashboard / manual changes still require this URL to receive events).
     */
    public function handleWebhook(Request $request)
    {
        $payload = json_decode($request->getContent(), true) ?: [];
        $type = $payload['type'] ?? null;

        match ($type) {
            'invoice.payment_succeeded' => Log::info('Stripe: payment succeeded', [
                'invoice_id' => $payload['data']['object']['id'] ?? null,
                'customer_id' => $payload['data']['object']['customer'] ?? null,
                'amount_paid' => $payload['data']['object']['amount_paid'] ?? null,
            ]),
            'invoice.payment_failed' => Log::warning('Stripe: payment failed', [
                'invoice_id' => $payload['data']['object']['id'] ?? null,
                'customer_id' => $payload['data']['object']['customer'] ?? null,
                'amount_due' => $payload['data']['object']['amount_due'] ?? null,
            ]),
            'charge.dispute.created' => Log::error('Stripe: dispute created', [
                'dispute_id' => $payload['data']['object']['id'] ?? null,
                'amount' => $payload['data']['object']['amount'] ?? null,
                'reason' => $payload['data']['object']['reason'] ?? null,
            ]),
            default => null,
        };

        return parent::handleWebhook($request);
    }

    protected function handleCustomerSubscriptionCreated(array $payload)
    {
        $response = parent::handleCustomerSubscriptionCreated($payload);

        $data = $payload['data']['object'];
        $tenant = $this->getUserByStripeId($data['customer'] ?? null);
        $sub = $tenant?->subscriptions()->where('stripe_id', $data['id'])->first();

        if ($tenant && $sub) {
            $this->subscriptionService->syncSubscriptionPlanFromStripe($tenant, $sub, $data);
        }

        return $response;
    }

    protected function handleCustomerSubscriptionUpdated(array $payload)
    {
        $response = parent::handleCustomerSubscriptionUpdated($payload);

        if ($response === null) {
            return null;
        }

        $data = $payload['data']['object'];
        $tenant = $this->getUserByStripeId($data['customer'] ?? null);
        $sub = $tenant?->subscriptions()->where('stripe_id', $data['id'])->first();

        if ($tenant && $sub) {
            $sub->refresh();
            $this->subscriptionService->syncSubscriptionPlanFromStripe($tenant, $sub, $data);
        }

        return $response;
    }
}
