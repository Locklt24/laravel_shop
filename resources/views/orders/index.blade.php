@extends('layouts.app')

@section('title', 'Đơn hàng của tôi')

@section('content')
    @php
        $statusLabels = [
            'pending' => 'Chờ xác nhận',
            'shipping' => 'Đang giao',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ];
    @endphp

    <section class="orders-page">
        <div class="orders-heading">
            <div>
                <span class="eyebrow">Mỗi đơn hàng, một niềm vui</span>
                <h1>Đơn hàng của tôi</h1>
                <p>Theo dõi hành trình những món đồ đang trên đường đến với bạn.</p>
            </div>
            <a class="orders-heading__link" href="{{ route('products') }}">
                Khám phá sản phẩm <span aria-hidden="true">↗</span>
            </a>
        </div>

        @if ($orders->isEmpty())
            <div class="orders-empty">
                <div class="orders-empty__icon" aria-hidden="true">
                    <svg viewBox="0 0 48 48" fill="none">
                        <path d="M8 14h32v27H8zM15 14V9h18v5M8 21h32M19 27h10" />
                    </svg>
                </div>
                <span class="eyebrow">Hành trình mới đang chờ</span>
                <h2>Bạn chưa có đơn hàng nào</h2>
                <p>Khi bạn đặt hàng, thông tin và trạng thái đơn sẽ được lưu tại đây.</p>
                <a class="button button--dark" href="{{ route('products') }}">
                    Bắt đầu mua sắm <span aria-hidden="true">→</span>
                </a>
            </div>
        @else
            <div class="orders-list">
                @foreach ($orders as $order)
                    @php
                        $statusClass = match ($order->status) {
                            'pending' => 'pending',
                            'shipping' => 'shipping',
                            'completed' => 'completed',
                            'cancelled' => 'cancelled',
                            default => 'pending',
                        };
                    @endphp

                    <article class="order-card">
                        <div class="order-card__top">
                            <div>
                                <span class="order-card__label">Mã đơn hàng</span>
                                <h2>{{ $order->code }}</h2>
                            </div>
                            <span class="order-status order-status--{{ $statusClass }}">
                                <span aria-hidden="true"></span>
                                {{ $statusLabels[$order->status] ?? $order->status }}
                            </span>
                        </div>

                        <div class="order-card__details">
                            <div>
                                <span class="order-card__label">Ngày đặt</span>
                                <strong>{{ $order->created_at->format('d/m/Y') }}</strong>
                            </div>
                            <div>
                                <span class="order-card__label">Người nhận</span>
                                <strong>{{ $order->receiver_name }}</strong>
                            </div>
                            <div>
                                <span class="order-card__label">Tổng thanh toán</span>
                                <strong class="order-card__total">
                                    {{ number_format($order->total_amount, 0, ',', '.') }}đ
                                </strong>
                            </div>
                        </div>

                        <div class="order-card__bottom">
                            <span>Thông tin sản phẩm và giao hàng trong chi tiết đơn.</span>
                            <a class="button button--dark" href="{{ route('orders.show', $order->id) }}">
                                Xem chi tiết <span aria-hidden="true">→</span>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
