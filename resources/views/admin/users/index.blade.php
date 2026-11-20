@extends('layouts.app')
@section('title', 'Quản lý người dùng')
@section('content')
    <section class="admin-dashboard-page">
        <header class="admin-page-heading">
            <div><span class="eyebrow">Khu vực quản trị</span><h1>Người dùng</h1></div>
        </header>
        @include('partials.admin-nav')

        @if (session('success'))
            <div class="notice notice--success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="notice notice--error">{{ session('error') }}</div>
        @endif

        <section class="admin-panel">
            <div class="admin-panel-heading admin-users-heading">
                <div><span class="eyebrow">Tài khoản</span><h2>Danh sách người dùng</h2></div>
                <form class="admin-search" method="GET" action="{{ route('admin.users.index') }}">
                    <input name="search" value="{{ request('search') }}" placeholder="Tìm tên hoặc email">
                    <button type="submit">Tìm kiếm</button>
                </form>
            </div>
            @if ($users->isEmpty())
                <p class="admin-panel-empty admin-panel-empty--boxed">Không tìm thấy người dùng.</p>
            @else
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Người dùng</th><th>Vai trò</th><th>Đơn hàng</th><th>Ngày tham gia</th><th>Thao tác</th></tr></thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td><strong>{{ $user->name }}</strong><small class="admin-table-email">{{ $user->email }}</small></td>
                                    <td><span class="admin-status admin-status--{{ $user->role }}">{{ $user->role === 'admin' ? 'Quản trị viên' : 'Thành viên' }}</span></td>
                                    <td>{{ $user->orders_count }}</td>
                                    <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="admin-table-actions">
                                            <form method="POST" action="{{ route('admin.users.update-role', $user) }}">
                                                @csrf @method('PATCH')
                                                <select name="role" aria-label="Vai trò của {{ $user->name }}" onchange="this.form.submit()">
                                                    <option value="user" @selected($user->role === 'user')>Thành viên</option>
                                                    <option value="admin" @selected($user->role === 'admin')>Quản trị viên</option>
                                                </select>
                                            </form>
                                            @unless ($user->is(auth()->user()))
                                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Xóa người dùng này?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit">Xóa</button>
                                                </form>
                                            @endunless
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="admin-pagination">{{ $users->links() }}</div>
            @endif
        </section>
    </section>
@endsection
