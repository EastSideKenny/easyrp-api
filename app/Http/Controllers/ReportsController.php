<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function revenue(Request $request): JsonResponse
    {
        [$from, $to] = $this->resolveDateRange($request);
        $groupBy = $this->resolveGroupBy($request);

        $rows = [];

        // Revenue + payment count from actual payments in date window.
        $payments = Payment::query()
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->get(['paid_at', 'amount']);

        foreach ($payments as $payment) {
            $periodDate = Carbon::parse((string) $payment->paid_at);
            $key = $this->periodKey($periodDate, $groupBy);
            $rows[$key] ??= $this->emptyRevenueRow($periodDate, $groupBy);
            $rows[$key]['revenue'] += (float) $payment->amount;
            $rows[$key]['payment_count']++;
        }

        // Invoice count by issue_date (commercial activity in that period).
        $invoices = Invoice::query()
            ->whereNull('deleted_at')
            ->whereNotNull('issue_date')
            ->where('status', '!=', 'canceled')
            ->whereBetween('issue_date', [$from->copy()->toDateString(), $to->copy()->toDateString()])
            ->get(['id', 'issue_date']);

        foreach ($invoices as $invoice) {
            $periodDate = Carbon::parse((string) $invoice->issue_date);
            $key = $this->periodKey($periodDate, $groupBy);
            $rows[$key] ??= $this->emptyRevenueRow($periodDate, $groupBy);
            $rows[$key]['invoice_count']++;
        }

        // COGS approximation from invoice items using product cost_price.
        $expenseRows = DB::connection('tenant')
            ->table('invoice_items as ii')
            ->join('invoices as i', 'i.id', '=', 'ii.invoice_id')
            ->leftJoin('products as p', 'p.id', '=', 'ii.product_id')
            ->whereNull('i.deleted_at')
            ->where('i.status', '!=', 'canceled')
            ->whereNotNull('i.issue_date')
            ->whereBetween('i.issue_date', [$from->copy()->toDateString(), $to->copy()->toDateString()])
            ->get([
                'i.issue_date',
                'ii.quantity',
                DB::raw('COALESCE(p.cost_price, 0) as cost_price'),
            ]);

        foreach ($expenseRows as $expense) {
            $periodDate = Carbon::parse((string) $expense->issue_date);
            $key = $this->periodKey($periodDate, $groupBy);
            $rows[$key] ??= $this->emptyRevenueRow($periodDate, $groupBy);
            $rows[$key]['expenses'] += (float) $expense->quantity * (float) $expense->cost_price;
        }

        ksort($rows);

        $payload = array_values(array_map(function (array $row): array {
            $row['revenue'] = round((float) $row['revenue'], 2);
            $row['expenses'] = round((float) $row['expenses'], 2);
            $row['profit'] = round((float) $row['revenue'] - (float) $row['expenses'], 2);
            $row['invoice_count'] = (int) $row['invoice_count'];
            $row['payment_count'] = (int) $row['payment_count'];
            return $row;
        }, $rows));

        return response()->json($payload);
    }

    public function salesByProduct(Request $request): JsonResponse
    {
        [$from, $to] = $this->resolveDateRange($request);
        $limit = max(1, min((int) $request->query('limit', 50), 100));

        $rows = DB::connection('tenant')
            ->table('invoice_items as ii')
            ->join('invoices as i', 'i.id', '=', 'ii.invoice_id')
            ->leftJoin('products as p', 'p.id', '=', 'ii.product_id')
            ->whereNull('i.deleted_at')
            ->where('i.status', '!=', 'canceled')
            ->whereNotNull('i.issue_date')
            ->whereBetween('i.issue_date', [$from->copy()->toDateString(), $to->copy()->toDateString()])
            ->groupBy('ii.product_id', 'p.name', 'p.sku', 'ii.description')
            ->selectRaw('
                COALESCE(ii.product_id, 0) as product_id,
                COALESCE(p.name, ii.description, \'Unnamed product\') as product_name,
                COALESCE(p.sku, \'\') as sku,
                COALESCE(SUM(ii.quantity), 0) as units_sold,
                COALESCE(SUM(ii.line_total), 0) as revenue,
                CASE
                    WHEN COALESCE(SUM(ii.quantity), 0) > 0
                    THEN COALESCE(SUM(ii.line_total), 0) / SUM(ii.quantity)
                    ELSE 0
                END as avg_price
            ')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();

        $payload = $rows->map(fn($row) => [
            'product_id' => (int) $row->product_id,
            'product_name' => (string) $row->product_name,
            'sku' => (string) $row->sku,
            'units_sold' => (int) round((float) $row->units_sold),
            'revenue' => round((float) $row->revenue, 2),
            'avg_price' => round((float) $row->avg_price, 2),
        ])->values();

        return response()->json($payload);
    }

    public function stockValue(): JsonResponse
    {
        $rows = Product::query()
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->get(['id', 'name', 'sku', 'stock_quantity', 'cost_price', 'price'])
            ->map(function (Product $product) {
                $costPrice = (float) ($product->cost_price ?? 0);
                $retailPrice = (float) ($product->price ?? 0);
                $qty = (int) ($product->stock_quantity ?? 0);
                return [
                    'product_id' => (int) $product->id,
                    'product_name' => (string) $product->name,
                    'sku' => (string) ($product->sku ?? ''),
                    'stock_quantity' => $qty,
                    'cost_price' => round($costPrice, 2),
                    'stock_value' => round($qty * $costPrice, 2),
                    'retail_value' => round($qty * $retailPrice, 2),
                ];
            })
            ->sortByDesc('stock_value')
            ->values();

        return response()->json($rows);
    }

    private function resolveDateRange(Request $request): array
    {
        $to = $request->filled('to')
            ? Carbon::parse((string) $request->query('to'))
            : Carbon::now();

        $from = $request->filled('from')
            ? Carbon::parse((string) $request->query('from'))
            : $to->copy()->subDays(30);

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy(), $from->copy()];
        }

        return [$from, $to];
    }

    private function resolveGroupBy(Request $request): string
    {
        $groupBy = (string) $request->query('group_by', 'month');
        return in_array($groupBy, ['day', 'week', 'month'], true) ? $groupBy : 'month';
    }

    private function periodKey(Carbon $date, string $groupBy): string
    {
        return match ($groupBy) {
            'day' => $date->copy()->startOfDay()->format('Y-m-d'),
            'week' => $date->copy()->startOfWeek()->format('Y-m-d'),
            default => $date->copy()->startOfMonth()->format('Y-m-01'),
        };
    }

    private function periodLabel(Carbon $date, string $groupBy): string
    {
        return match ($groupBy) {
            'day' => $date->format('Y-m-d'),
            'week' => sprintf(
                '%s to %s',
                $date->copy()->startOfWeek()->format('M d, Y'),
                $date->copy()->endOfWeek()->format('M d, Y')
            ),
            default => $date->format('M Y'),
        };
    }

    private function emptyRevenueRow(Carbon $date, string $groupBy): array
    {
        return [
            'period' => $this->periodLabel($date, $groupBy),
            'revenue' => 0.0,
            'expenses' => 0.0,
            'profit' => 0.0,
            'invoice_count' => 0,
            'payment_count' => 0,
        ];
    }
}
