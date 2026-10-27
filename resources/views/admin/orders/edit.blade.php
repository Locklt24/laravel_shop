@extends('layouts.app')

@section('title', 'Cập nhật đơn hàng')

@section('content')
    <section class="catalog-page">
        <div class="catalog-heading">
            <div>
                <span class="eyebrow">Quản lý đơn hàng</span>
                <h1>Cập nhật trạng thái</h1>
                <p>Mã đơn: <strong>{{ $order->code }}</strong></p>
            </div>
            <a class="cart-continue" href="{{ route('admin.orders.index') }}">
                <span aria-hidden="true">←</span> Quay lại danh sách
            </a>
        </div>

        @if ($errors->has('status'))
            <div class="notice notice--error" role="alert">{{ $errors->first('status') }}</div>
        @endif

        <div class="account-details">
            <p>
                Trạng thái hiện tại:
                <strong>
                    {{ [
                        'pending' => 'Chờ xác nhận',
                        'shipping' => 'Đang giao',
                        'completed' => 'Hoàn thành',
                        'cancelled' => 'Đã hủy',
                    ][$order->status] ?? $order->status }}
                </strong>
            </p>

            @if (count($allowedTransitions) > 0)
                @php
                    $statusLabels = [
                        'shipping' => 'Đang giao',
                        'completed' => 'Hoàn thành',
                        'cancelled' => 'Đã hủy',
                    ];
                @endphp

                <form action="{{ route('admin.orders.update', $order->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <label class="d-block mb-2" for="status">Chuyển sang trạng thái</label>
                    <select class="form-select mb-3" id="status" name="status" required>
                        @foreach ($allowedTransitions as $status)
                            <option value="{{ $status }}" @selected(old('status', $allowedTransitions[0]) === $status)>
                                {{ $statusLabels[$status] }}
                            </option>
                        @endforeach
                    </select>

                    <button class="button button--dark" type="submit">Lưu trạng thái</button>
                </form>
            @else
                <p class="account-details__note">
                    Đơn hàng đã ở trạng thái cuối cùng và không thể thay đổi thêm.
                </p>
            @endif
        </div>
    </section>

@endsection