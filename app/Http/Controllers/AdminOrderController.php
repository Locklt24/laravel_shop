<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminOrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('user')->latest()->get();

        return view('admin.orders.index', compact('orders'));
    }

    public function edit(Order $order)
    {
        return view('admin.orders.edit', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,shipping,completed,cancelled',
        ]);

        DB::transaction(function () use ($data, $order) {
            $order->load('items');

            // Hủy đơn thì hoàn lại tồn kho một lần
            if ($data['status'] === 'cancelled' && $order->status !== 'cancelled') {
                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        Product::where('id', $item->product_id)
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