<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVariantCart
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $cart = $request->session()->get('cart', []);

        foreach ($cart as $item) {
            if (! is_array($item) || ! array_key_exists('color', $item) || ! array_key_exists('size', $item)) {
                $request->session()->put('legacy_cart', $cart);
                $request->session()->forget('cart');

                return redirect()->route('cart.index')->with(
                    'error',
                    'Giỏ hàng cũ chưa chọn màu/size. Vui lòng thêm lại sản phẩm với biến thể mong muốn.'
                );
            }
        }

        return $next($request);
    }
}
