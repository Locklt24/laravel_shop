@extends('layouts.app')

@section('title', 'Tổng quan quản trị')

@section('content')
    <section class="admin-dashboard-page">
        <header class="admin-page-heading">
            <div>
                <span class="eyebrow">Bảng điều khiển</span>
                <h1>Tổng quan cửa hàng</h1>
                <p>Theo dõi doanh thu, đơn hàng và hoạt động sản phẩm.</p>
            </div>
            <span class="admin-page-heading__date">{{ now()->format('d/m/Y') }}</span>
        </header>

        @include('partials.admin-nav')

        <div class="admin-summary-grid">
            <article class="admin-summary-card admin-summary-card--revenue">
                <span class="admin-summary-card__label">Doanh thu hoàn thành</span>
                <strong>{{ number_format($summary['revenue'], 0, ',', '.') }}đ</strong>
                <span class="admin-summary-card__note">Hôm nay: {{ number_format($summary['today_revenue'], 0, ',', '.') }}đ</span>
            </article>
            <article class="admin-summary-card">
                <span class="admin-summary-card__label">Đơn hàng</span>
                <strong>{{ number_format($summary['orders']) }}</strong>
                <span class="admin-summary-card__note">{{ $summary['pending_orders'] }} đơn chờ xử lý</span>
            </article>
            <article class="admin-summary-card">
                <span class="admin-summary-card__label">Sản phẩm</span>
                <strong>{{ number_format($summary['products']) }}</strong>
                <span class="admin-summary-card__note">{{ $summary['categories'] }} danh mục</span>
            </article>
            <article class="admin-summary-card">
                <span class="admin-summary-card__label">Người dùng</span>
                <strong>{{ number_format($summary['users']) }}</strong>
                <span class="admin-summary-card__note">Tài khoản đã đăng ký</span>
            </article>
        </div>

        <div class="admin-dashboard-grid">
            <section class="admin-panel admin-revenue-panel" aria-labelledby="revenue-title">
                <div class="admin-panel-heading">
                    <div>
                        <span class="eyebrow">6 tháng gần nhất</span>
                        <h2 id="revenue-title">Doanh thu</h2>
                    </div>
                </div>
                @php
                    $chartMax = max(1, (float) $revenueChart->max('value'));
                @endphp
                <div class="admin-revenue-chart" aria-label="Biểu đồ doanh thu theo tháng">
                    @foreach ($revenueChart as $month)
                        @php
                            $height = $month['value'] > 0
                                ? max(8, round(($month['value'] / $chartMax) * 100))
                                : 4;
                        @endphp
                        <div class="admin-revenue-chart__column">
                            <span class="admin-revenue-chart__value">{{ number_format($month['value'], 0, ',', '.') }}đ</span>
                            <div class="admin-revenue-chart__bar" style="height: {{ $height }}%"></div>
                            <span class="admin-revenue-chart__label">{{ $month['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="admin-panel" aria-labelledby="category-title">
                <div class="admin-panel-heading">
                    <div>
                        <span class="eyebrow">Phân bố kho hàng</span>
                        <h2 id="category-title">Danh mục</h2>
                    </div>
                    <a class="admin-panel-link" href="{{ route('admin.categories.index') }}">Quản lý →</a>
                </div>
                <div class="admin-category-list">
                    @forelse ($categoryStats as $category)
                        <div class="admin-category-row">
                            <span>{{ $category->name }}</span>
                            <strong>{{ $category->products_count }} sản phẩm</strong>
                        </div>
                    @empty
                        <p class="admin-panel-empty">Chưa có danh mục sản phẩm.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <section class="admin-panel" aria-labelledby="recent-orders-title">
            <div class="admin-panel-heading">
                <div>
                    <span class="eyebrow">Hoạt động mới nhất</span>
                    <h2 id="recent-orders-title">Đơn hàng gần đây</h2>
                </div>
                <a class="admin-panel-link" href="{{ route('admin.orders.index') }}">Xem tất cả →</a>
            </div>
            @if ($recentOrders->isEmpty())
                <p class="admin-panel-empty admin-panel-empty--boxed">Chưa có đơn hàng nào.</p>
            @else
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr><th>Đơn hàng</th><th>Khách hàng</th><th>Ngày đặt</th><th>Tổng tiền</th><th>Trạng thái</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($recentOrders as $order)
                                <tr>
                                    <td><strong>{{ $order->code }}</strong></td>
                                    <td>{{ $order->user->name }}</td>
                                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                    <td><strong>{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong></td>
                                    <td><span class="admin-status admin-status--{{ $order->status }}">{{ $order->status }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </section>
@endsection
