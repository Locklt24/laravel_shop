@extends('layouts.app')

@section('title', 'Quản lý đơn hàng')

@section('content')
    @php
        $statusLabels = [
            'pending' => 'Chờ xác nhận',
            'shipping' => 'Đang giao',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ];
    @endphp

    <section class="admin-orders-page">
        <header class="admin-orders-heading">
            <div>
                <span class="eyebrow">Khu vực quản trị</span>
                <h1>Quản lý đơn hàng</h1>
                <p>Theo dõi đơn mới, cập nhật tiến trình và quản lý giao hàng.</p>
            </div>
            <span class="admin-orders-heading__date">
                <span aria-hidden="true">◷</span> {{ now()->format('d/m/Y') }}
            </span>
        </header>

        @if (session('success'))
            <div class="notice notice--success" role="status">{{ session('success') }}</div>
        @endif

        <div class="admin-orders-summary" aria-label="Tổng quan đơn hàng">
            <article class="admin-orders-stat admin-orders-stat--total">
                <span class="admin-orders-stat__icon" aria-hidden="true">▤</span>
                <span class="admin-orders-stat__label">Tổng đơn hàng</span>
                <strong>{{ number_format($summary['total']) }}</strong>
                <span class="admin-orders-stat__note">Tất cả đơn đã đặt</span>
            </article>
            <article class="admin-orders-stat admin-orders-stat--pending">
                <span class="admin-orders-stat__icon" aria-hidden="true">◷</span>
                <span class="admin-orders-stat__label">Chờ xác nhận</span>
                <strong>{{ number_format($summary['pending']) }}</strong>
                <span class="admin-orders-stat__note">Cần được xử lý</span>
            </article>
            <article class="admin-orders-stat admin-orders-stat--shipping">
                <span class="admin-orders-stat__icon" aria-hidden="true">↗</span>
                <span class="admin-orders-stat__label">Đang giao</span>
                <strong>{{ number_format($summary['shipping']) }}</strong>
                <span class="admin-orders-stat__note">Đang trên đường đến khách</span>
            </article>
            <article class="admin-orders-stat admin-orders-stat--completed">
                <span class="admin-orders-stat__icon" aria-hidden="true">✓</span>
                <span class="admin-orders-stat__label">Hoàn thành</span>
                <strong>{{ number_format($summary['completed']) }}</strong>
                <span class="admin-orders-stat__note">Giao hàng thành công</span>
            </article>
        </div>

        <section class="admin-orders-panel" aria-labelledby="admin-orders-list-title">
            <div class="admin-orders-panel__heading">
                <div>
                    <span class="eyebrow">Hoạt động gần đây</span>
                    <h2 id="admin-orders-list-title">Danh sách đơn hàng</h2>
                </div>
                <span class="admin-orders-panel__count">{{ $orders->count() }} đơn hàng</span>
            </div>

            @if ($orders->isEmpty())
                <div class="admin-orders-empty">
                    <span aria-hidden="true">▤</span>
                    <h3>Chưa có đơn hàng nào</h3>
                    <p>Đơn hàng mới sẽ xuất hiện tại đây sau khi khách hàng hoàn tất thanh toán.</p>
                </div>
            @else
                <div class="admin-orders-table-wrap">
                    <table class="admin-orders-table">
                        <thead>
                            <tr>
                                <th scope="col">Đơn hàng</th>
                                <th scope="col">Khách hàng</th>
                                <th scope="col">Ngày đặt</th>
                                <th scope="col">Tổng tiền</th>
                                <th scope="col">Trạng thái</th>
                                <th scope="col"><span class="visually-hidden">Thao tác</span></th>
                            </tr>
                        </thead>
                        <tbody>
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
                                <tr>
                                    <td data-label="Đơn hàng">
                                        <span class="admin-orders-code">{{ $order->code }}</span>
                                        <span class="admin-orders-id">#{{ $order->id }}</span>
                                    </td>
                                    <td data-label="Khách hàng">
                                        <span class="admin-orders-customer">
                                            <span class="admin-orders-avatar" aria-hidden="true">
                                                {{ Illuminate\Support\Str::of($order->user->name)->substr(0, 1)->upper() }}
                                            </span>
                                            <span>
                                                <strong>{{ $order->user->name }}</strong>
                                                <small>{{ $order->user->email }}</small>
                                            </span>
                                        </span>
                                    </td>
                                    <td data-label="Ngày đặt">
                                        <span class="admin-orders-date">{{ $order->created_at->format('d/m/Y') }}</span>
                                        <span class="admin-orders-time">{{ $order->created_at->format('H:i') }}</span>
                                    </td>
                                    <td data-label="Tổng tiền">
                                        <strong class="admin-orders-total">
                                            {{ number_format($order->total_amount, 0, ',', '.') }}đ
                                        </strong>
                                    </td>
                                    <td data-label="Trạng thái">
                                        <span class="order-status order-status--{{ $statusClass }}">
                                            <span aria-hidden="true"></span>
                                            {{ $statusLabels[$order->status] ?? $order->status }}
                                        </span>
                                    </td>
                                    <td data-label="Thao tác">
                                        <a
                                            class="admin-orders-action"
                                            href="{{ route('admin.orders.edit', $order) }}"
                                            aria-label="Cập nhật trạng thái đơn {{ $order->code }}"
                                        >
                                            Cập nhật <span aria-hidden="true">→</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </section>
@endsection