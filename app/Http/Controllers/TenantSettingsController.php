<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TenantSettingsController extends Controller
{
    /**
     * Get tenant branding settings.
     */
    public function show(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        return response()->json([
            'name' => $tenant->name,
            'subdomain' => $tenant->subdomain,
            'currency' => $tenant->currency,
            'theme' => $tenant->theme,
            'supplier_address_line_1' => $tenant->supplier_address_line_1,
            'supplier_address_line_2' => $tenant->supplier_address_line_2,
            'supplier_city' => $tenant->supplier_city,
            'supplier_postal_code' => $tenant->supplier_postal_code,
            'supplier_country' => $tenant->supplier_country,
            'supplier_vat_number' => $tenant->supplier_vat_number,
            'logo_url' => $tenant->logo_path ? Storage::disk('public')->url($tenant->logo_path) : null,
        ]);
    }

    /**
     * Update tenant branding settings.
     */
    public function update(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'theme' => ['sometimes', 'string', 'in:default,blue,green,purple,rose,orange'],
            'supplier_address_line_1' => ['nullable', 'string', 'max:255'],
            'supplier_address_line_2' => ['nullable', 'string', 'max:255'],
            'supplier_city' => ['nullable', 'string', 'max:255'],
            'supplier_postal_code' => ['nullable', 'string', 'max:255'],
            'supplier_country' => ['nullable', 'string', 'max:255'],
            'supplier_vat_number' => ['nullable', 'string', 'max:255'],
        ]);

        if (isset($validated['currency'])) {
            $validated['currency'] = strtoupper($validated['currency']);
        }

        $tenant->update($validated);

        return response()->json([
            'message' => 'Settings updated.',
            'name' => $tenant->name,
            'subdomain' => $tenant->subdomain,
            'currency' => $tenant->currency,
            'theme' => $tenant->theme,
            'supplier_address_line_1' => $tenant->supplier_address_line_1,
            'supplier_address_line_2' => $tenant->supplier_address_line_2,
            'supplier_city' => $tenant->supplier_city,
            'supplier_postal_code' => $tenant->supplier_postal_code,
            'supplier_country' => $tenant->supplier_country,
            'supplier_vat_number' => $tenant->supplier_vat_number,
            'logo_url' => $tenant->logo_path ? Storage::disk('public')->url($tenant->logo_path) : null,
        ]);
    }

    /**
     * Upload tenant logo.
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => ['required', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
        ]);

        $tenant = $request->user()->tenant;

        // Delete old logo
        if ($tenant->logo_path) {
            Storage::disk('public')->delete($tenant->logo_path);
        }

        $path = $request->file('logo')->store("logos/{$tenant->id}", 'public');
        $tenant->update(['logo_path' => $path]);

        return response()->json([
            'message' => 'Logo uploaded.',
            'logo_url' => Storage::disk('public')->url($path),
        ]);
    }

    /**
     * Delete tenant logo.
     */
    public function deleteLogo(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if ($tenant->logo_path) {
            Storage::disk('public')->delete($tenant->logo_path);
            $tenant->update(['logo_path' => null]);
        }

        return response()->json(['message' => 'Logo removed.']);
    }
}
