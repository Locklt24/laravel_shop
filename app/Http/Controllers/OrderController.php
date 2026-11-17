<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
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
}
