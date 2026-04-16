<?php

namespace App\Http\Controllers;

use App\Models\WebshopSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebshopSettingController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $settings = WebshopSetting::first();

        if (! $settings) {
            return response()->json(['message' => 'Webshop settings not configured.'], 404);
        }

        return response()->json($settings);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'store_name' => ['nullable', 'string', 'max:255'],
            'primary_color' => ['sometimes', 'string', 'max:7'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'enable_guest_checkout' => ['sometimes', 'boolean'],
            'stripe_public_key' => ['nullable', 'string', 'max:255'],
            'stripe_secret_key' => ['nullable', 'string', 'max:255'],
        ]);

        $settings = WebshopSetting::updateOrCreate([], $validated);

        return response()->json($settings);
    }
}
