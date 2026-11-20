<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $completedRevenue = (float) Order::where('status', 'completed')->sum('total_amount');
        $todayRevenue = (float) Order::where('status', 'completed')
            ->whereDate('created_at', today())
            ->sum('total_amount');

        $summary = [
            'revenue' => $completedRevenue,
            'today_revenue' => $todayRevenue,
            'orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'products' => Product::count(),
            'categories' => Category::count(),
            'users' => User::count(),
        ];

        $recentOrders = Order::with('user')
            ->latest()
            ->limit(6)
            ->get();

        $categoryStats = Category::withCount('products')
            ->orderByDesc('products_count')
            ->limit(6)
            ->get();

        $monthlyRevenue = Order::where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->get(['created_at', 'total_amount'])
            ->groupBy(fn (Order $order): string => $order->created_at->format('Y-m'))
            ->map(fn ($orders): float => (float) $orders->sum('total_amount'));

        $revenueChart = collect(range(5, 0))
            ->map(function (int $monthsAgo) use ($monthlyRevenue): array {
                $month = now()->subMonths($monthsAgo);
                $key = $month->format('Y-m');

                return [
                    'label' => $month->format('m/Y'),
                    'value' => $monthlyRevenue->get($key, 0),
                ];
            });

        return view('admin.dashboard', compact(
            'summary',
            'recentOrders',
            'categoryStats',
            'revenueChart',
        ));
    }
}
