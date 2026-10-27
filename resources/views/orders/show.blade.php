@extends('layouts.app')

@section('title', 'Chi tiết đơn hàng')

@section('content')
    @php
        $statusLabels = [
            'pending' => 'Chờ xác nhận',
            'shipping' => 'Đang giao',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ];
        $statusClass = match ($order->status) {
            'pending' => 'pending',
            'shipping' => 'shipping',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
            default => 'pending',
        };
        $statusSteps = [
            'pending' => 1,
            'shipping' => 2,
            'completed' => 3,
        ];
        $currentStep = $statusSteps[$order->status] ?? 0;
    @endphp

    <section class="order-detail-page">
        <a class="order-detail-back" href="{{ route('orders.index') }}">
            <span aria-hidden="true">←</span> Quay lại đơn hàng
        </a>

        @if (session('success'))
            <div class="notice notice--success" role="status">{{ session('success') }}</div>
        @endif

        <header class="order-detail-heading">
            <div>
                <span class="eyebrow">{{ session('success') ? 'Cảm ơn bạn đã mua sắm' : 'Thông tin đơn hàng' }}</span>
                <h1>{{ session('success') ? 'Đặt hàng thành công' : 'Chi tiết đơn hàng' }}</h1>
                <p>Mã đơn <strong>{{ $order->code }}</strong><span aria-hidden="true">·</span> Đặt ngày {{ $order->created_at->format('d/m/Y') }}</p>
            </div>
            <span class="order-status order-status--{{ $statusClass }}">
                <span aria-hidden="true"></span>
                {{ $statusLabels[$order->status] ?? $order->status }}
            </span>
        </header>

        @if ($order->status === 'cancelled')
            <div class="order-cancelled" role="status">
                <span aria-hidden="true">×</span>
                <div>
                    <strong>Đơn hàng đã được hủy</strong>
                    <p>Đơn hàng này hiện không còn được xử lý.</p>
                </div>
            </div>
        @else
            <section class="order-progress" aria-label="Tiến trình đơn hàng">
                <div class="order-progress__step {{ $currentStep >= 1 ? 'is-complete' : '' }}">
                    <span class="order-progress__marker" aria-hidden="true">{{ $currentStep > 1 ? '✓' : '1' }}</span>
                    <span>Chờ xác nhận</span>
                </div>
                <span class="order-progress__line {{ $currentStep >= 2 ? 'is-complete' : '' }}" aria-hidden="true"></span>
                <div class="order-progress__step {{ $currentStep >= 2 ? 'is-complete' : '' }}">
                    <span class="order-progress__marker" aria-hidden="true">{{ $currentStep > 2 ? '✓' : '2' }}</span>
                    <span>Đang giao</span>
                </div>
                <span class="order-progress__line {{ $currentStep >= 3 ? 'is-complete' : '' }}" aria-hidden="true"></span>
                <div class="order-progress__step {{ $currentStep >= 3 ? 'is-complete' : '' }}">
                    <span class="order-progress__marker" aria-hidden="true">3</span>
                    <span>Hoàn thành</span>
                </div>
            </section>
        @endif

        <div class="order-detail-layout">
            <div class="order-detail-main">
                <section class="order-detail-card" aria-labelledby="order-items-title">
                    <div class="order-detail-card__heading">
                        <div>
                            <span class="eyebrow">Đã chọn cho bạn</span>
                            <h2 id="order-items-title">Sản phẩm trong đơn</h2>
                        </div>
                        <span>{{ $order->items->count() }} sản phẩm</span>
                    </div>

                    <div class="order-lines">
                        @foreach ($order->items as $item)
                            <article class="order-line">
                                <div class="order-line__image" aria-hidden="true">SL</div>
                                <div class="order-line__details">
                                    <h3>{{ $item->product_name }}</h3>
                                    @if ($item->color || $item->size)
                                        <p>
                                            @if ($item->color && $item->size)
                                                ({{ $item->color }} / {{ $item->size }})
                                            @else
                                                {{ $item->color ?? $item->size }}
                                            @endif
                                        </p>
                                    @endif
                                    <span>{{ number_format($item->price, 0, ',', '.') }}đ × {{ $item->quantity }}</span>
                                </div>
                                <strong class="order-line__subtotal">
                                    {{ number_format($item->subtotal, 0, ',', '.') }}đ
                                </strong>
                            </article>
                        @endforeach
                    </div>

                    <div class="order-total">
                        <span>Tổng thanh toán</span>
                        <strong>{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong>
                    </div>
                </section>
            </div>

            <aside class="order-recipient" aria-labelledby="order-recipient-title">
                <span class="eyebrow">Giao đến</span>
                <h2 id="order-recipient-title">Thông tin nhận hàng</h2>

                <div class="order-recipient__person">
                    <span class="order-recipient__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="8" r="3.5" />
                            <path d="M5 20a7 7 0 0 1 14 0" />
                        </svg>
                    </span>
                    <div>
                        <span>Người nhận</span>
                        <strong>{{ $order->receiver_name }}</strong>
                    </div>
                </div>

                <div class="order-recipient__person">
                    <span class="order-recipient__icon order-recipient__icon--sage" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M7 3h10v18l-5-3-5 3V3Z" />
                            <path d="M10 8h4" />
                        </svg>
                    </span>
                    <div>
                        <span>Số điện thoại</span>
                        <strong>{{ $order->phone }}</strong>
                    </div>
                </div>

                <div class="order-recipient__person">
                    <span class="order-recipient__icon order-recipient__icon--dark" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z" />
                            <circle cx="12" cy="10" r="2" />
                        </svg>
                    </span>
                    <div>
                        <span>Địa chỉ giao hàng</span>
                        <strong>{{ $order->address }}</strong>
                    </div>
                </div>

                <a class="order-recipient__link" href="{{ route('orders.index') }}">
                    Tất cả đơn hàng <span aria-hidden="true">→</span>
                </a>
            </aside>
        </div>
    </section>
@endsection
