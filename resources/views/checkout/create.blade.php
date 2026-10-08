@extends('layouts.app')

@section('title', 'Thanh toán')

@section('content')
    <h1>Thanh toán</h1>

    @error('cart')
        <p style="color: red;">{{ $message }}</p>
    @enderror

    <h3>Sản phẩm đã chọn</h3>

    @foreach ($cart as $item)
        <p>
            {{ $item['name'] }}
            × {{ $item['quantity'] }}
            — {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}đ
        </p>
    @endforeach

    <h2>Tổng tiền: {{ number_format($total, 0, ',', '.') }}đ</h2>

    <form action="{{ route('checkout.store') }}" method="POST">
        @csrf

        <label>Người nhận:</label><br>
        <input
            type="text"
            name="receiver_name"
            value="{{ old('receiver_name', auth()->user()->name) }}"
        >
        @error('receiver_name') <p style="color:red">{{ $message }}</p> @enderror

        <br><br>

        <label>Số điện thoại:</label><br>
        <input type="text" name="phone" value="{{ old('phone') }}">
        @error('phone') <p style="color:red">{{ $message }}</p> @enderror

        <br><br>

        <label>Địa chỉ giao hàng:</label><br>
        <textarea name="address">{{ old('address') }}</textarea>
        @error('address') <p style="color:red">{{ $message }}</p> @enderror

        <br><br>

        <button type="submit">Xác nhận đặt hàng</button>
    </form>
@endsection