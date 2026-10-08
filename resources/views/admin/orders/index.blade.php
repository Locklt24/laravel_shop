@extends('layouts.app')

@section('title', 'Quản lý đơn hàng')

@section('content')
    <h1>Quản lý đơn hàng</h1>

    @if (session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    @foreach ($orders as $order)
        <div style="border: 1px solid #ccc; padding: 15px; margin-bottom: 10px;">
            <p>Mã đơn: <strong>{{ $order->code }}</strong></p>
            <p>Khách hàng: {{ $order->user->name }}</p>
            <p>Trạng thái: {{ $order->status }}</p>
            <p>Tổng tiền: {{ number_format($order->total_amount, 0, ',', '.') }}đ</p>

            <a href="{{ route('admin.orders.edit', $order->id) }}">
                Cập nhật trạng thái
            </a>
        </div>
    @endforeach
@endsection