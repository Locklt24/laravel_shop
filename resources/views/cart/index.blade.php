@extends('layouts.app')

@section('title', 'Giỏ hàng')

@section('content')
    <h1>Giỏ hàng</h1>

    @if (session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    @if (session('error'))
        <p style="color: red;">{{ session('error') }}</p>
    @endif

    @if (empty($cart))
        <p>Giỏ hàng của bạn đang trống.</p>
        <a href="{{ route('products') }}">Xem sản phẩm</a>
    @else
        @foreach ($cart as $item)
            <div style="border: 1px solid #ccc; padding: 15px; margin-bottom: 10px;">
                @if ($item['image'])
                    <img
                        src="{{ asset('storage/' . $item['image']) }}"
                        alt="{{ $item['name'] }}"
                        width="100"
                    >
                @endif

                <h3>{{ $item['name'] }}</h3>
                <p>Giá: {{ number_format($item['price'], 0, ',', '.') }}đ</p>

                <form action="{{ route('cart.update', $item['id']) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <label>Số lượng:</label>
                    <input
                        type="number"
                        name="quantity"
                        min="1"
                        value="{{ $item['quantity'] }}"
                    >
                    <button type="submit">Cập nhật</button>
                </form>

                <p>
                    Tạm tính:
                    <strong>
                        {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}đ
                    </strong>
                </p>

                <form action="{{ route('cart.remove', $item['id']) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Xóa</button>
                </form>
            </div>
        @endforeach

        <h2>Tổng tiền: {{ number_format($total, 0, ',', '.') }}đ</h2>
        @auth
            <a href="{{ route('checkout.create') }}">
                <button>Tiến hành thanh toán</button>
            </a>
        @else
            <a href="{{ route('login') }}">Đăng nhập để thanh toán</a>
        @endauth
    @endif
@endsection