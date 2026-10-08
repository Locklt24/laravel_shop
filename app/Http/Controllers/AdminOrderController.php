<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminOrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::with('user')->latest()->get();

        return view('admin.orders.index', compact('orders'));
    }

    public function edit(Order $order): View
    {
        return view('admin.orders.edit', compact('order'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => 'required|in:pending,shipping,completed,cancelled',
        ]);

        DB::transaction(function () use ($data, $order): void {
            $order = Order::lockForUpdate()->findOrFail($order->id);
            $allowedTransitions = [
                'pending' => ['shipping', 'cancelled'],
                'shipping' => ['completed', 'cancelled'],
                'completed' => [],
                'cancelled' => [],
            ];

            abort_unless(
                in_array($data['status'], $allowedTransitions[$order->status] ?? [], true),
                422,
                'Không thể chuyển sang trạng thái này.'
            );

            if ($data['status'] === 'cancelled') {
                $items = $order->items()->orderBy('id')->get();
                $variantIds = $items->pluck('product_variant_id')->filter()->unique()->all();
                $variants = ProductVariant::whereKey($variantIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($items as $item) {
                    if ($item->product_variant_id || $item->color !== null || $item->size !== null) {
                        $variant = $variants->get($item->product_variant_id);

                        abort_unless($variant, 409, 'Không thể hoàn kho: biến thể đã bị xóa.');

                        $variant->increment('stock', $item->quantity);
                    } elseif ($item->product_id) {
                        Product::whereKey($item->product_id)
                            ->increment('stock', $item->quantity);
                    }
                }
            }

            $order->update([
                'status' => $data['status'],
            ]);
        });

        return redirect()
            ->route('admin.orders.index')
            ->with('success', 'Đã cập nhật trạng thái đơn hàng.');
    }
}
