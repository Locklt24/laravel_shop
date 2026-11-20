<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function show(Request $request, Order $order): View
    {
        $user = $request->user();

        abort_unless($user instanceof User, 401);

        $isOwner = $order->user_id === $user->getKey();
        $isAdmin = $user->role === 'admin';

        abort_unless($isOwner || $isAdmin, 403);

        $order->load('items');

        return view('orders.show', compact('order'));
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user instanceof User, 401);

        $orders = $user
            ->orders()
            ->latest()
            ->get();

        return view('orders.index', compact('orders'));
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User, 401);
        abort_unless($order->user_id === $user->getKey(), 403);

        if ($order->status !== 'pending') {
            return back()->with('error', 'Chỉ có thể hủy đơn hàng đang chờ xác nhận.');
        }

        DB::transaction(function () use ($order): void {
            $order = Order::with('items')->lockForUpdate()->findOrFail($order->id);

            if ($order->status !== 'pending') {
                abort(409, 'Đơn hàng không còn ở trạng thái chờ xác nhận.');
            }

            $variantIds = $order->items
                ->pluck('product_variant_id')
                ->filter()
                ->unique()
                ->all();
            $variants = ProductVariant::whereKey($variantIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    $variant = $variants->get($item->product_variant_id);
                    abort_unless($variant, 409, 'Không thể hoàn kho: biến thể đã bị xóa.');
                    $variant->increment('stock', $item->quantity);
                } elseif ($item->product_id) {
                    Product::whereKey($item->product_id)
                        ->increment('stock', $item->quantity);
                }
            }

            $order->update([
                'status' => 'cancelled',
                'payment_status' => $order->payment_status === 'paid'
                    ? 'refunded'
                    : 'pending',
            ]);
        });

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Đã hủy đơn hàng và hoàn kho sản phẩm.');
    }
}
