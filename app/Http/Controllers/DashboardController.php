<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Models\Products;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function statistics()
    {
        $today = Carbon::today();

        // Today's Revenue
        $todaysRevenue = Transaction::whereDate('created_at', $today)->sum('total');

        // Today's Transactions
        $todaysTransactions = Transaction::whereDate('created_at', $today)->count();

        // Products Sold Today
        $productSoldToday = TransactionItem::whereHas('transaction', function ($query) use ($today) {
            $query->whereDate('created_at', $today);
        })->sum('quantity');

        // Revenue Chart (30 hari terakhir)
        $startDate = Carbon::today()->subDays(29);

        $revenueChart = Transaction::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total) as revenue')
        )
            ->whereDate('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'revenue' => (float) $item->revenue,
                ];
            });

        $revenueChartFilled = [];

        for ($i = 0; $i < 30; $i++) {

            $date = $startDate->copy()->addDays($i)->format('Y-m-d');

            $found = $revenueChart->firstWhere('date', $date);

            $revenueChartFilled[] = [
                'date' => $date,
                'revenue' => $found ? $found['revenue'] : 0,
            ];
        }

        // Best Selling Products
        $bestSellingProducts = TransactionItem::select(
            'product_id',
            DB::raw('SUM(quantity) as total_sold')
        )
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->with('product:id,name,image,price')
            ->get()
            ->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'name' => $item->product?->name,
                    'image' => asset('storage/'.$item->product?->image),
                    'price' => $item->product?->price,
                    'total_sold' => (int) $item->total_sold,
                ];
            });

        // Low Stock Products
        $lowStockProducts = Products::where('stock', '<=', 10)
            ->orderBy('stock', 'asc')
            ->limit(10)
            ->get(['id', 'name', 'image', 'stock', 'price'])
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image' => asset('storage/'.$product->image),
                    'stock' => $product->stock,
                    'price' => $product->price,
                ];
            });

        return ApiResponse::success([
            'todays_revenue' => (float) $todaysRevenue,
            'todays_transactions' => $todaysTransactions,
            'product_sold_today' => (int) $productSoldToday,
            'revenue_chart' => $revenueChartFilled,
            'best_selling_products' => $bestSellingProducts,
            'low_stock_products' => $lowStockProducts,
        ], 'Dashboard Statistics');
    }
}
