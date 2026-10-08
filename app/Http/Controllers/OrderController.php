<?php

namespace App\Http\Controllers;

use App\Models\Order;

class OrderController extends Controller
{
    public function show(Order $order)
    {
        $isOwner = $order->user_id === auth()->id();
        $isAdmin = auth()->user()->role === 'admin';

        abort_unless($isOwner || $isAdmin, 403);

        $order->load('items');

        return view('orders.show', compact('order'));
    }
    public function index()
    {
        $orders = auth()->user()
            ->orders()
            ->latest()
            ->get();

        return view('orders.index', compact('orders'));
    }
}