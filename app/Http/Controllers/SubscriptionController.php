<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\PlanLimitService;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly PlanLimitService $limits,
        private readonly SubscriptionService $subscriptionService
    ) {}

    /**
     * GET /api/subscriptions
     *
     * Returns the tenant's current subscription (paid or free) plus a usage snapshot.
     * Onboarding flows hit this before a tenant exists; we return an empty payload then
     * so the SPA does not see a 404.
     */
    public function index(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if (! $tenant) {
            return response()->json([
                'subscriptions' => [],
                'usage'         => [],
                'trial_ends_at' => null,
                'status'        => 'none',
            ]);
        }

        $payload = $this->subscriptionService->serializeSubscription($tenant);

        return response()->json([
            'subscriptions' => $payload ? [$payload] : [],
            'usage'         => $this->limits->getUsageSnapshot($tenant),
            'trial_ends_at' => $payload['trial_ends_at'] ?? null,
            'status'        => $payload['status'] ?? 'none',
        ]);
    }

    /**
     * GET /api/subscriptions/current
     */
    public function current(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if (! $tenant) {
            return response()->json(['subscription' => null]);
        }

        return response()->json([
            'subscription' => $this->subscriptionService->serializeSubscription($tenant),
            'usage'        => $this->limits->getUsageSnapshot($tenant),
        ]);
    }

    public function listPlans(): JsonResponse
    {
        return response()->json($this->subscriptionService->getAllPlans());
    }

    public function getPlan(Plan $plan): JsonResponse
    {
        return response()->json($this->subscriptionService->getPlanPricing($plan));
    }

    /**
     * POST /api/subscriptions/subscribe-paid
     */
    public function subscribeToPaid(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if (! $tenant) {
            return response()->json(['message' => 'No tenant found.'], 404);
        }

        $validated = $request->validate([
            'plan_id'           => 'required|exists:plans,id',
            'payment_method_id' => 'required|string',
            'billing_cycle'     => 'sometimes|in:monthly,yearly',
            'trial_days'        => 'sometimes|integer|min:0|max:0',
        ]);

        try {
            $plan = Plan::findOrFail($validated['plan_id']);

            $this->subscriptionService->subscribeToPaidPlan(
                $tenant,
                $plan,
                $validated['payment_method_id'],
                $validated['billing_cycle'] ?? 'monthly',
                0
            );

            return response()->json([
                'success'      => true,
                'message'      => "Successfully subscribed to {$plan->name}",
                'subscription' => $this->subscriptionService->serializeSubscription($tenant->refresh()),
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * POST /api/subscriptions/subscribe-free
     */
    public function subscribeToFree(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if (! $tenant) {
            return response()->json(['message' => 'No tenant found.'], 404);
        }

        $validated = $request->validate([
            'plan_id'    => 'required|exists:plans,id',
            'trial_days' => 'sometimes|integer|min:0|max:0',
        ]);

        try {
            $plan = Plan::findOrFail($validated['plan_id']);

            $this->subscriptionService->subscribeToFreePlan(
                $tenant,
                $plan,
                0
            );

            return response()->json([
                'success'      => true,
                'message'      => "Successfully subscribed to {$plan->name}",
                'subscription' => $this->subscriptionService->serializeSubscription($tenant->refresh()),
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * POST /api/subscriptions/change-plan
     */
    public function changePlan(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if (! $tenant) {
            return response()->json(['message' => 'No tenant found.'], 404);
        }

        $validated = $request->validate([
            'plan_id'           => 'required|exists:plans,id',
            'billing_cycle'     => 'sometimes|in:monthly,yearly',
            'payment_method_id' => 'sometimes|string',
            'trial_days'        => 'sometimes|integer|min:0|max:0',
        ]);

        try {
            $newPlan = Plan::findOrFail($validated['plan_id']);

            $this->subscriptionService->changePlan(
                $tenant,
                $newPlan,
                $validated['billing_cycle'] ?? 'monthly',
                $validated['payment_method_id'] ?? null,
                0
            );

            return response()->json([
                'success'      => true,
                'message'      => 'Subscription plan changed successfully',
                'subscription' => $this->subscriptionService->serializeSubscription($tenant->refresh()),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * POST /api/subscriptions/resume — undo cancel-at-period-end during the grace window.
     */
    public function resume(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if (! $tenant) {
            return response()->json(['message' => 'No tenant found.'], 404);
        }

        try {
            $this->subscriptionService->resumeSubscription($tenant);

            return response()->json([
                'success'      => true,
                'message'      => 'Your subscription will continue renewing as usual.',
                'subscription' => $this->subscriptionService->serializeSubscription($tenant->refresh()),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * DELETE /api/subscriptions
     */
    public function cancel(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if (! $tenant) {
            return response()->json(['message' => 'No tenant found.'], 404);
        }

        $validated = $request->validate([
            'immediately' => 'sometimes|boolean',
        ]);

        try {
            $this->subscriptionService->cancelSubscription(
                $tenant,
                $validated['immediately'] ?? false
            );

            return response()->json([
                'success'      => true,
                'message'      => 'Subscription canceled successfully',
                'subscription' => $this->subscriptionService->serializeSubscription($tenant->refresh()),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
