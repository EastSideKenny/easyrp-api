<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\OfferResponseController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\TenantSettingsController;
use App\Http\Controllers\UserManagementController;
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
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\WebshopSettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Stripe webhook — must match Dashboard URL (default Laravel api prefix → `/api/stripe/webhook`).
Route::post('/stripe/webhook', [WebhookController::class, 'handleWebhook'])
    ->withoutMiddleware(['auth:sanctum']);

// Public auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
Route::post('/reset-password', [PasswordResetController::class, 'reset']);

// Public offer response (accept/decline via email link — schema resolved internally)
Route::post('/offers/respond', [OfferResponseController::class, 'respond']);

// Public tenant resolve
Route::get('/tenants/resolve/{subdomain}', [TenantController::class, 'resolve']);

// Public storefront routes (no auth required, schema set by subdomain)
Route::prefix('storefront/{subdomain}')->middleware('tenant.schema')->group(function () {
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

    // Tenant creation (no schema yet)
    Route::post('/tenants', [TenantController::class, 'store']);

    // Plans & Features (public/read-only for auth users)
    Route::get('/plans', [PlanController::class, 'index']);
    Route::get('/plans/{plan}', [PlanController::class, 'show']);
    Route::get('/features', [FeatureController::class, 'index']);

    // Subscription routes
    Route::get('/subscription-plans', [SubscriptionController::class, 'listPlans']);
    Route::get('/subscription-plans/{plan}', [SubscriptionController::class, 'getPlan']);

    // Site admin routes
    Route::middleware('site.admin')->prefix('admin')->group(function () {
        Route::get('/stats', [AdminController::class, 'stats']);
        Route::get('/tenants', [AdminController::class, 'tenants']);
        Route::get('/tenants/{tenant}', [AdminController::class, 'showTenant']);
        Route::patch('/tenants/{tenant}/plan', [AdminController::class, 'updateTenantPlan']);
        Route::patch('/tenants/{tenant}/toggle-status', [AdminController::class, 'toggleTenantStatus']);
        Route::get('/plans', [AdminController::class, 'plans']);
    });

    // Routes that require tenant schema
    Route::middleware('tenant.schema')->group(function () {
        // Tenant
        Route::get('/tenant', [TenantController::class, 'show']);
        Route::patch('/tenant', [TenantController::class, 'update']);

        // Subscriptions (tenant-scoped — Cashier handles all Stripe-side state)
        Route::get('/subscriptions', [SubscriptionController::class, 'index']);
        Route::get('/subscriptions/current', [SubscriptionController::class, 'current']);
        Route::post('/subscriptions/subscribe-paid', [SubscriptionController::class, 'subscribeToPaid']);
        Route::post('/subscriptions/subscribe-free', [SubscriptionController::class, 'subscribeToFree']);
        Route::post('/subscriptions/change-plan', [SubscriptionController::class, 'changePlan']);
        Route::post('/subscriptions/resume', [SubscriptionController::class, 'resume']);
        Route::delete('/subscriptions', [SubscriptionController::class, 'cancel']);

        // Tenant settings (owner/admin only, accessible even with expired trial)
        Route::middleware('tenant.admin')->group(function () {
            Route::get('/settings/branding', [TenantSettingsController::class, 'show']);
            Route::patch('/settings/branding', [TenantSettingsController::class, 'update']);
            Route::post('/settings/branding/logo', [TenantSettingsController::class, 'uploadLogo']);
            Route::delete('/settings/branding/logo', [TenantSettingsController::class, 'deleteLogo']);

            Route::get('/settings/users', [UserManagementController::class, 'index']);
            Route::post('/settings/users', [UserManagementController::class, 'store']);
            Route::patch('/settings/users/{user}', [UserManagementController::class, 'update']);
            Route::delete('/settings/users/{user}', [UserManagementController::class, 'destroy']);
        });

        // Active subscription / trial — each subgroup also checks plan_features pivot codes.
        Route::middleware('trial.active')->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'stats']);

            Route::middleware('plan.feature:products')->group(function () {
                Route::apiResource('products', ProductController::class);
                Route::get('/products/{product}/in-use', [ProductController::class, 'inUse']);
                Route::apiResource('product-categories', ProductCategoryController::class);
            });

            Route::middleware('plan.feature:customers')->group(function () {
                Route::apiResource('customers', CustomerController::class);
            });

            Route::middleware('plan.feature:invoices')->group(function () {
                Route::apiResource('invoices', InvoiceController::class);
                Route::post('/invoices/{invoice}/pay', [InvoiceController::class, 'pay']);
                Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'send']);
                Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf']);
            });

            Route::middleware('plan.feature:payments')->group(function () {
                Route::get('/payments', [PaymentController::class, 'index']);
                Route::get('/payments/{payment}', [PaymentController::class, 'show']);
                Route::post('/payments', [PaymentController::class, 'store']);
                Route::delete('/payments/{payment}', [PaymentController::class, 'destroy']);
            });

            Route::middleware('plan.feature:inventory')->group(function () {
                Route::get('/stock-movements', [StockMovementController::class, 'index']);
                Route::get('/stock-movements/{stockMovement}', [StockMovementController::class, 'show']);
                Route::post('/stock-movements', [StockMovementController::class, 'store']);
                Route::get('/inventory', [InventoryController::class, 'index']);
            });

            Route::middleware('plan.feature:offers')->group(function () {
                Route::apiResource('offers', OfferController::class);
                Route::post('/offers/{offer}/send', [OfferController::class, 'send']);
                Route::post('/offers/{offer}/convert-to-invoice', [OfferController::class, 'convertToInvoice']);
                Route::get('/offers/{offer}/pdf', [OfferController::class, 'pdf']);
                Route::post('/offers/{offer}/accept', [OfferController::class, 'accept']);
                Route::post('/offers/{offer}/decline', [OfferController::class, 'decline']);
            });

            Route::middleware('plan.feature:orders')->group(function () {
                Route::apiResource('orders', OrderController::class);
            });

            Route::middleware('plan.feature:storefront')->group(function () {
                Route::get('/webshop-settings', [WebshopSettingController::class, 'show']);
                Route::patch('/webshop-settings', [WebshopSettingController::class, 'update']);
            });

            Route::get('/setup-progress', [SetupProgressController::class, 'index']);
            Route::patch('/setup-progress/{step}', [SetupProgressController::class, 'update']);
        });
    }); // end tenant.schema
});
