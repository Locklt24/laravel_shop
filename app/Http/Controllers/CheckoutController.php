<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    private const FREE_SHIPPING_THRESHOLD = 500000;

    private const SHIPPING_FEE = 30000;

    public function create(): View|RedirectResponse
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Giỏ hàng đang trống.');
        }

        $total = collect($cart)->sum(function (array $item): float {
            return $item['price'] * $item['quantity'];
        });
        $shippingFee = $this->shippingFee($total);
        $grandTotal = $total + $shippingFee;

        return view('checkout.create', compact('cart', 'total', 'shippingFee', 'grandTotal'));
    }

    public function store(Request $request): RedirectResponse
    {
        $provinces = array_column(
            json_decode(
                file_get_contents(public_path('data/vietnam-provinces.json')),
                true,
                flags: JSON_THROW_ON_ERROR
            ),
            'name'
        );

        $data = $request->validate([
            'receiver_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'province' => ['required', 'string', 'max:100', Rule::in($provinces)],
            'ward' => 'required|string|max:100',
            'shipping_note' => 'nullable|string|max:500',
            'payment_method' => 'required|in:cod,bank_transfer',
        ], [
            'province.in' => 'Vui lòng chọn tỉnh/thành phố theo danh sách hiện hành.',
        ]);

        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Giỏ hàng đang trống.');
        }

        $order = DB::transaction(function () use ($cart, $data, $request): Order {
            $total = 0;

            $variantIds = array_keys($cart);
            sort($variantIds, SORT_NUMERIC);
            $variants = ProductVariant::with('product')
                ->whereKey($variantIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($variantIds as $variantId) {
                $item = $cart[$variantId];
                $requestedQuantity = $item['quantity'] ?? null;
                $quantity = is_int($requestedQuantity) || is_string($requestedQuantity)
                    ? filter_var($requestedQuantity, FILTER_VALIDATE_INT, [
                        'options' => ['min_range' => 1],
                    ])
                    : false;
                $variant = $variants->get($variantId);

                if (! $variant || ! $variant->product || $quantity === false || $quantity > $variant->stock) {
                    throw ValidationException::withMessages([
                        'cart' => 'Một sản phẩm trong giỏ đã hết hàng hoặc không còn hợp lệ.',
                    ]);
                }

                $total += $variant->product->price * $quantity;
            }

            $shippingFee = $this->shippingFee($total);

            $order = Order::create([
                'user_id' => $request->user()->id,
                'code' => 'DH-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4)),
                'receiver_name' => $data['receiver_name'],
                'phone' => $data['phone'],
                'address' => collect([$data['ward'], $data['province']])->filter()->join(', '),
                'province' => $data['province'],
                'ward' => $data['ward'],
                'shipping_note' => $data['shipping_note'] ?? null,
                'total_amount' => $total + $shippingFee,
                'status' => 'pending',
                'payment_method' => $data['payment_method'],
                'payment_status' => 'pending',
                'shipping_fee' => $shippingFee,
            ]);

            foreach ($variantIds as $variantId) {
                $variant = $variants[$variantId];
                $quantity = (int) $cart[$variantId]['quantity'];
                $price = $variant->product->price;

                $order->items()->create([
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'color' => $variant->color,
                    'size' => $variant->size,
                    'price' => $price,
                    'quantity' => $quantity,
                    'subtotal' => $price * $quantity,
                ]);

                $variant->decrement('stock', $quantity);
            }

            return $order;
        });

        session()->forget('cart');

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Đặt hàng thành công!');
    }

    private function shippingFee(float $subtotal): int
    {
        return $subtotal >= self::FREE_SHIPPING_THRESHOLD
            ? 0
            : self::SHIPPING_FEE;
    }
}
