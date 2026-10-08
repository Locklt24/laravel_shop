@extends('layouts.app')

@section('title', 'Cập nhật đơn hàng')

@section('content')
    <h1>Cập nhật đơn {{ $order->code }}</h1>

    <form action="{{ route('admin.orders.update', $order->id) }}" method="POST">
        @csrf
        @method('PUT')

        <label>Trạng thái:</label><br>

        <select name="status">
            <option value="pending" @selected($order->status === 'pending')>
                Chờ xác nhận
            </option>

            <option value="shipping" @selected($order->status === 'shipping')>
                Đang giao
            </option>

            <option value="completed" @selected($order->status === 'completed')>
                Thành công
            </option>

            <option value="cancelled" @selected($order->status === 'cancelled')>
                Đã hủy
            </option>
        </select>

        <br><br>

        <button type="submit">Lưu trạng thái</button>
    </form>
@endsection