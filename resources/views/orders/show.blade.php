@extends('layouts.app')

@section('title', 'Đơn hàng')

@section('content')
    @if (session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <h1>Đặt hàng thành công</h1>

    <p>Mã đơn: <strong>{{ $order->code }}</strong></p>
    <p>Người nhận: {{ $order->receiver_name }}</p>
    <p>Số điện thoại: {{ $order->phone }}</p>
    <p>Địa chỉ: {{ $order->address }}</p>
    <p>Trạng thái: Chờ xác nhận</p>

    <h3>Chi tiết đơn hàng</h3>

    @foreach ($order->items as $item)
        <p>
            {{ $item->product_name }}
            @if ($item->color && $item->size)
                ({{ $item->color }} / {{ $item->size }})
            @endif
            × {{ $item->quantity }}
            — {{ number_format($item->subtotal, 0, ',', '.') }}đ
        </p>
    @endforeach

    <h2>Tổng tiền: {{ number_format($order->total_amount, 0, ',', '.') }}đ</h2>
@endsection
