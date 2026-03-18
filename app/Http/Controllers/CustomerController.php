<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\PlanLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        return response()->json(Customer::where('tenant_id', $tenant->id)->get());
    }

    public function show(Request $request, Customer $customer): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($customer->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($customer);
    }

    public function store(Request $request, PlanLimitService $limits): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($limits->isAtLimit($tenant, 'customers')) {
            return response()->json([
                'message'  => 'You have reached the customer limit for your plan. Please upgrade to add more.',
                'error'    => 'limit_reached',
                'resource' => 'customers',
                'limit'    => $limits->getLimit($tenant, 'customers'),
                'used'     => $limits->getUsage($tenant, 'customers'),
            ], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:255'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $customer = Customer::create(array_merge($validated, ['tenant_id' => $tenant->id]));

        return response()->json($customer, 201);
    }

    public function update(Request $request, Customer $customer): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($customer->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:255'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $customer->update($validated);

        return response()->json($customer);
    }

    public function destroy(Request $request, Customer $customer): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($customer->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $customer->delete();

        return response()->json(['message' => 'Customer deleted.']);
    }
}
