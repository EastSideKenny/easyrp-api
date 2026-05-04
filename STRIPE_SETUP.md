# Stripe Cashier Integration Setup Guide

## Overview

You have successfully installed Laravel Cashier with Stripe integration. Your application now supports:

- **Paid Plans**: Starter & Pro (with Stripe billing)
- **Free Plans**: Free Trial & Exclusive (managed locally without Stripe)

## Quick Setup Steps

### 1. Run Migrations

```bash
php artisan migrate
```

This will add the necessary Stripe columns to your `tenants` and `plans` tables:

- `tenants`: `stripe_id`, `pm_type`, `pm_last_four`, `trial_ends_at`
- `plans`: `stripe_product_id`, `stripe_price_monthly_id`, `stripe_price_yearly_id`

### 2. Add Stripe Product IDs to Your Plans

#### Get Your Stripe Product & Price IDs

1. Go to **Stripe Dashboard** → **Products** (or https://dashboard.stripe.com/products)
2. Find your "Starter" plan product
3. Copy the **Product ID** (starts with `prod_`)
4. Note the **Price IDs** for monthly & yearly (starts with `price_`)
5. Repeat for "Pro" plan

#### Update Plans in Your Database

Use one of these approaches:

**Option A: Database GUI**

```sql
UPDATE plans SET
  stripe_product_id = 'prod_xxxxx',
  stripe_price_monthly_id = 'price_xxx',
  stripe_price_yearly_id = 'price_yyy'
WHERE slug = 'starter';

UPDATE plans SET
  stripe_product_id = 'prod_yyyyy',
  stripe_price_monthly_id = 'price_aaa',
  stripe_price_yearly_id = 'price_bbb'
WHERE slug = 'pro';
```

**Option B: Laravel Tinker**

```bash
php artisan tinker
```

```php
$starter = Plan::where('slug', 'starter')->first();
$starter->update([
  'stripe_product_id' => 'prod_xxxxx',
  'stripe_price_monthly_id' => 'price_xxx',
  'stripe_price_yearly_id' => 'price_yyy'
]);

$pro = Plan::where('slug', 'pro')->first();
$pro->update([
  'stripe_product_id' => 'prod_yyyyy',
  'stripe_price_monthly_id' => 'price_aaa',
  'stripe_price_yearly_id' => 'price_bbb'
]);
```

**Option C: Create a Seeder**
Create `database/seeders/UpdatePlansWithStripeIds.php`:

```php
<?php
namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class UpdatePlansWithStripeIds extends Seeder
{
    public function run(): void
    {
        Plan::where('slug', 'starter')->update([
            'stripe_product_id' => 'prod_xxxxx',
            'stripe_price_monthly_id' => 'price_xxx',
            'stripe_price_yearly_id' => 'price_yyy'
        ]);

        Plan::where('slug', 'pro')->update([
            'stripe_product_id' => 'prod_yyyyy',
            'stripe_price_monthly_id' => 'price_aaa',
            'stripe_price_yearly_id' => 'price_bbb'
        ]);
    }
}
```

Then run:

```bash
php artisan db:seed --class=UpdatePlansWithStripeIds
```

### 3. Configure Stripe Webhook

#### Set Webhook Endpoint in Stripe Dashboard

1. Go to **Stripe Dashboard** → **Developers** → **Webhooks**
2. Click **Add endpoint**
3. Enter: `https://your-domain.com/api/stripe/webhook`
4. Select events to listen for:
    - `customer.subscription.created`
    - `customer.subscription.updated`
    - `customer.subscription.deleted`
    - `invoice.payment_succeeded`
    - `invoice.payment_failed`
    - `charge.dispute.created`
5. Copy the **Signing Secret** and add to `.env`:

```
CASHIER_SECRET=whsec_xxxxxxxxxxxxxxxxxxxxxxx
```

#### For Local Testing with ngrok

```bash
# Terminal 1: Run your Laravel app
php artisan serve

# Terminal 2: Expose via ngrok
ngrok http 8000

# Use the generated URL in Stripe webhook settings
https://abc123.ngrok.io/api/stripe/webhook
```

Then test webhooks with Stripe CLI:

```bash
stripe listen --forward-to localhost:8000/api/stripe/webhook
```

### 4. API Endpoints

#### Get Available Plans

```http
GET /api/subscription-plans
Authorization: Bearer {token}
```

Response:

```json
[
  {
    "id": 1,
    "name": "Starter",
    "slug": "starter",
    "is_free": false,
    "monthly": {
      "price": 2999,
      "stripe_price_id": "price_xxx"
    },
    "yearly": {
      "price": 29999,
      "stripe_price_id": "price_yyy"
    },
    "features": [...]
  }
]
```

#### Subscribe to Paid Plan

```http
POST /api/subscriptions/subscribe-paid
Authorization: Bearer {token}
Content-Type: application/json

{
  "plan_id": 1,
  "payment_method_id": "pm_1234567890",
  "billing_cycle": "monthly",
  "trial_days": 14
}
```

**Getting `payment_method_id`** (from Frontend):

```javascript
// Use Stripe.js to create a payment method
const { paymentMethod } = await stripe.createPaymentMethod({
    type: "card",
    card: cardElement,
});
// Send paymentMethod.id to your backend
```

#### Subscribe to Free Plan

```http
POST /api/subscriptions/subscribe-free
Authorization: Bearer {token}
Content-Type: application/json

{
  "plan_id": 4,
  "trial_days": 7
}
```

#### Change Subscription Plan

```http
POST /api/subscriptions/{subscription}/change-plan
Authorization: Bearer {token}
Content-Type: application/json

{
  "plan_id": 2,
  "billing_cycle": "yearly"
}
```

#### Cancel Subscription

```http
DELETE /api/subscriptions/{subscription}
Authorization: Bearer {token}

{
  "immediately": false
}
```

#### Get Current Subscription

```http
GET /api/subscriptions
Authorization: Bearer {token}
```

## File Summary

### Created/Modified Files

- ✅ `app/Models/Tenant.php` — Added `Billable` trait
- ✅ `app/Models/Plan.php` — Added Stripe columns to Fillable
- ✅ `database/migrations/2026_05_04_000000_add_cashier_columns_to_tenants_table.php`
- ✅ `database/migrations/2026_05_04_000001_add_stripe_ids_to_plans_table.php`
- ✅ `app/Services/SubscriptionService.php` — Core subscription logic
- ✅ `app/Http/Controllers/SubscriptionController.php` — API endpoints
- ✅ `app/Http/Controllers/WebhookController.php` — Stripe webhook handling
- ✅ `routes/api.php` — Added subscription and webhook routes

## About Your Plans

### Paid Plans (Starter & Pro)

- Must have Stripe product IDs configured
- Customers enter payment method before subscribing
- Stripe handles billing cycles automatically
- You can offer trial periods

### Free Plans (Free Trial & Exclusive)

- No Stripe product needed
- No payment method required
- Can have a local "trial" period
- Status tracked only in your database

**Recommendation**: Keep free plans local-only (simpler management). You can always add them to Stripe later if needed.

## Troubleshooting

### "Payment incomplete for subscription" error

- Ensure payment method is valid and 3D Secure (if required) passes
- Check Stripe dashboard for decline reasons

### Webhook not triggering

- Verify signing secret in `.env`
- Check webhook endpoint is accessible from internet
- Enable test events in Stripe Dashboard

### Subscription not updating after Stripe change

- Webhooks may be queued; check `QUEUE_CONNECTION` in `.env`
- Manually sync: Check logs in `storage/logs/`

## Next Steps

1. **Run migrations**: `php artisan migrate`
2. **Add Stripe product IDs** from Stripe Dashboard to your plans
3. **Update webhook secret** in `.env`
4. **Test in Stripe test mode** before going live
5. **Build frontend** payment form using Stripe.js

## Resources

- [Laravel Cashier Docs](https://laravel.com/docs/cashier-stripe)
- [Stripe API Documentation](https://stripe.com/docs/api)
- [Stripe Testing Cards](https://stripe.com/docs/testing)
