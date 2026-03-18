<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SetupProgressController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\WebshopSettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public tenant resolve
Route::get('/tenants/resolve/{subdomain}', [TenantController::class, 'resolve']);

// Public storefront routes (no auth required)
Route::prefix('storefront/{subdomain}')->group(function () {
    Route::get('/settings', [StorefrontController::class, 'settings']);
    Route::get('/products', [StorefrontController::class, 'products']);
    Route::get('/products/{product}', [StorefrontController::class, 'product']);
    Route::get('/categories', [StorefrontController::class, 'categories']);
});

// Protected routes (require Sanctum token)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user()->load('tenant');
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'stats']);

    // Tenant
    Route::post('/tenants', [TenantController::class, 'store']);
    Route::get('/tenant', [TenantController::class, 'show']);
    Route::patch('/tenant', [TenantController::class, 'update']);

    // Plans & Features (public/read-only for auth users)
    Route::get('/plans', [PlanController::class, 'index']);
    Route::get('/plans/{plan}', [PlanController::class, 'show']);
    Route::get('/features', [FeatureController::class, 'index']);

    // Subscriptions
    Route::get('/subscriptions', [SubscriptionController::class, 'index']);
    Route::get('/subscriptions/{subscription}', [SubscriptionController::class, 'show']);

    // All routes below require an active subscription / non-expired trial
    Route::middleware('trial.active')->group(function () {
        // Products
        Route::apiResource('products', ProductController::class);

        // Product Categories
        Route::apiResource('product-categories', ProductCategoryController::class);

        // Customers
        Route::apiResource('customers', CustomerController::class);

        // Invoices
        Route::apiResource('invoices', InvoiceController::class);
        Route::post('/invoices/{invoice}/pay', [InvoiceController::class, 'pay']);

        // Payments
        Route::get('/payments', [PaymentController::class, 'index']);
        Route::get('/payments/{payment}', [PaymentController::class, 'show']);
        Route::post('/payments', [PaymentController::class, 'store']);
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy']);

        // Stock Movements
        Route::get('/stock-movements', [StockMovementController::class, 'index']);
        Route::get('/stock-movements/{stockMovement}', [StockMovementController::class, 'show']);
        Route::post('/stock-movements', [StockMovementController::class, 'store']);

        // Inventory
        Route::get('/inventory', [InventoryController::class, 'index']);

        // Offers
        Route::apiResource('offers', OfferController::class);
        Route::post('/offers/{offer}/convert-to-invoice', [OfferController::class, 'convertToInvoice']);
        Route::get('/offers/{offer}/pdf', [OfferController::class, 'pdf']);

        // Orders
        Route::apiResource('orders', OrderController::class);

        // Webshop Settings
        Route::get('/webshop-settings', [WebshopSettingController::class, 'show']);
        Route::patch('/webshop-settings', [WebshopSettingController::class, 'update']);

        // Setup Progress
        Route::get('/setup-progress', [SetupProgressController::class, 'index']);
        Route::patch('/setup-progress/{step}', [SetupProgressController::class, 'update']);
    });
});
