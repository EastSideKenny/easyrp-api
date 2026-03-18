<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;
        $tenantId = $tenant->id;
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $thirtyDaysAgo = $now->copy()->subDays(30);
        $sixtyDaysAgo = $now->copy()->subDays(60);

        // Revenue (last 30 days) from payments
        $revenue30d = Payment::where('tenant_id', $tenantId)
            ->where('paid_at', '>=', $thirtyDaysAgo)
            ->sum('amount');

        // Revenue (previous 30 days) for comparison
        $revenuePrev30d = Payment::where('tenant_id', $tenantId)
            ->where('paid_at', '>=', $sixtyDaysAgo)
            ->where('paid_at', '<', $thirtyDaysAgo)
            ->sum('amount');

        // Invoices this month
        $invoicesThisMonth = Invoice::where('tenant_id', $tenantId)
            ->where('created_at', '>=', $startOfMonth)
            ->count();

        // Unpaid invoices (outstanding)
        $unpaidTotal = Invoice::where('tenant_id', $tenantId)
            ->whereIn('status', ['draft', 'sent'])
            ->sum('total');

        // New customers this month
        $newCustomers = Customer::where('tenant_id', $tenantId)
            ->where('created_at', '>=', $startOfMonth)
            ->count();

        // Recent invoices (last 5)
        $recentInvoices = Invoice::where('tenant_id', $tenantId)
            ->with('customer:id,name')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn(Invoice $invoice) => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer' => $invoice->customer?->name,
                'date' => $invoice->issue_date?->format('M d, Y'),
                'amount' => round((float) $invoice->total, 2),
                'status' => $invoice->status,
            ]);

        // Low stock alerts
        $lowStockProducts = Product::where('tenant_id', $tenantId)
            ->where('track_inventory', true)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('stock_quantity', '<=', 0)
                    ->orWhereColumn('stock_quantity', '<=', 'low_stock_threshold');
            })
            ->orderBy('stock_quantity')
            ->limit(10)
            ->get()
            ->map(fn(Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'qty' => $product->stock_quantity,
                'status' => $product->stock_quantity <= 0 ? 'out_of_stock' : 'low_stock',
            ]);

        return response()->json([
            'summary' => [
                'revenue_30d' => round((float) $revenue30d, 2),
                'revenue_prev_30d' => round((float) $revenuePrev30d, 2),
                'invoices_this_month' => $invoicesThisMonth,
                'unpaid_total' => round((float) $unpaidTotal, 2),
                'new_customers' => $newCustomers,
            ],
            'recent_invoices' => $recentInvoices,
            'low_stock_alerts' => $lowStockProducts,
        ]);
    }
}
