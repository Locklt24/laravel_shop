<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->withCount('orders')
            ->when($request->string('search')->trim()->toString(), function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', 'in:admin,user'],
        ]);

        if ($user->is(auth()->user()) && $data['role'] !== 'admin') {
            return back()->with('error', 'Không thể tự hạ quyền tài khoản đang đăng nhập.');
        }

        $user->role = $data['role'];
        $user->save();

        return back()->with('success', 'Đã cập nhật vai trò người dùng.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', 'Không thể tự xóa tài khoản đang đăng nhập.');
        }

        if ($user->orders()->exists()) {
            return back()->with('error', 'Không thể xóa tài khoản đã có lịch sử đơn hàng.');
        }

        $user->delete();

        return back()->with('success', 'Đã xóa người dùng.');
    }
}
