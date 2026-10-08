@extends('layouts.app')

@section('title', 'Đơn hàng của tôi')

@section('content')
    <h1>Đơn hàng của tôi</h1>

    @forelse ($orders as $order)
        <div style="border: 1px solid #ccc; padding: 15px; margin-bottom: 10px;">
            <p>Mã đơn: <strong>{{ $order->code }}</strong></p>
            <p>Trạng thái: {{ $order->status }}</p>
            <p>Tổng tiền: {{ number_format($order->total_amount, 0, ',', '.') }}đ</p>

            <a href="{{ route('orders.show', $order->id) }}">
                Xem chi tiết
            </a>
        </div>
    @empty
        <p>Bạn chưa có đơn hàng nào.</p>
    @endforelse
@endsection