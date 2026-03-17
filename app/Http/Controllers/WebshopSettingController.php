<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebshopSettingController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if (! $tenant) {
            return response()->json(['message' => 'No tenant found.'], 404);
        }

        $settings = $tenant->webshopSetting;

        if (! $settings) {
            return response()->json(['message' => 'Webshop settings not configured.'], 404);
        }

        return response()->json($settings);
    }

    public function update(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if (! $tenant) {
            return response()->json(['message' => 'No tenant found.'], 404);
        }

        $validated = $request->validate([
            'store_name' => ['nullable', 'string', 'max:255'],
            'primary_color' => ['sometimes', 'string', 'max:7'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'enable_guest_checkout' => ['sometimes', 'boolean'],
            'stripe_public_key' => ['nullable', 'string', 'max:255'],
            'stripe_secret_key' => ['nullable', 'string', 'max:255'],
        ]);

        $settings = $tenant->webshopSetting()->updateOrCreate(
            ['tenant_id' => $tenant->id],
            $validated
        );

        return response()->json($settings);
    }
}
