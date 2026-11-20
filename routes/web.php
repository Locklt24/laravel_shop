<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\WishlistController;
use App\Http\Middleware\EnsureVariantCart;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/san-pham', [ProductController::class, 'index'])
    ->name('products');

Route::middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/san-pham/them', [ProductController::class, 'create'])
        ->name('products.create');

    Route::post('/san-pham', [ProductController::class, 'store'])
        ->name('products.store');

    Route::get('/san-pham/{product}/sua', [ProductController::class, 'edit'])
        ->name('products.edit');

    Route::put('/san-pham/{product}', [ProductController::class, 'update'])
        ->name('products.update');

    Route::delete('/san-pham/{product}', [ProductController::class, 'destroy'])
        ->name('products.destroy');
});

Route::get('/san-pham/{product}', [ProductController::class, 'show'])
    ->name('products.show');

Route::post('/san-pham/{product}/danh-gia', [ProductReviewController::class, 'store'])
    ->middleware('auth')
    ->name('products.reviews.store');
use App\Http\Controllers\AuthController;

Route::middleware('guest')->group(function () {
    Route::get('/dang-ky', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/dang-ky', [AuthController::class, 'register'])
        ->name('register.store');

    Route::get('/dang-nhap', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/dang-nhap', [AuthController::class, 'login'])
        ->name('login.store');

    Route::get('/quen-mat-khau', [AuthController::class, 'showForgotPassword'])
        ->name('password.request');

    Route::post('/quen-mat-khau', [AuthController::class, 'sendPasswordResetLink'])
        ->name('password.email');

    Route::get('/dat-lai-mat-khau/{token}', [AuthController::class, 'showResetPassword'])
        ->name('password.reset');

    Route::post('/dat-lai-mat-khau', [AuthController::class, 'resetPassword'])
        ->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/tai-khoan', function () {
        if (auth()->user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return view('dashboard');
    })->name('dashboard');

    Route::post('/dang-xuat', [AuthController::class, 'logout'])
        ->name('logout');

    Route::get('/don-hang-cua-toi', [OrderController::class, 'index'])
        ->name('orders.index');

    Route::get('/doi-mat-khau', [AuthController::class, 'showChangePassword'])
        ->name('password.change');

    Route::put('/doi-mat-khau', [AuthController::class, 'changePassword'])
        ->name('password.change.update');

    Route::post('/don-hang/{order}/huy', [OrderController::class, 'cancel'])
        ->name('orders.cancel');

    Route::get('/yeu-thich', [WishlistController::class, 'index'])
        ->name('wishlist.index');

    Route::post('/san-pham/{product}/yeu-thich', [WishlistController::class, 'toggle'])
        ->name('wishlist.toggle');
});
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/san-pham', [AdminProductController::class, 'index'])
        ->name('admin.products.index');

    Route::get('/admin', [AdminDashboardController::class, 'index'])
        ->name('admin.dashboard');

    Route::resource('/admin/danh-muc', CategoryController::class)
        ->except(['show'])
        ->parameters(['danh-muc' => 'category'])
        ->names([
            'index' => 'admin.categories.index',
            'create' => 'admin.categories.create',
            'store' => 'admin.categories.store',
            'edit' => 'admin.categories.edit',
            'update' => 'admin.categories.update',
            'destroy' => 'admin.categories.destroy',
        ]);

    Route::get('/admin/nguoi-dung', [AdminUserController::class, 'index'])
        ->name('admin.users.index');
    Route::patch('/admin/nguoi-dung/{user}/vai-tro', [AdminUserController::class, 'updateRole'])
        ->name('admin.users.update-role');
    Route::delete('/admin/nguoi-dung/{user}', [AdminUserController::class, 'destroy'])
        ->name('admin.users.destroy');

    Route::get('/admin/don-hang', [AdminOrderController::class, 'index'])
        ->name('admin.orders.index');

    Route::get('/admin/don-hang/{order}/sua', [AdminOrderController::class, 'edit'])
        ->name('admin.orders.edit');

    Route::put('/admin/don-hang/{order}', [AdminOrderController::class, 'update'])
        ->name('admin.orders.update');
});
Route::middleware(EnsureVariantCart::class)->group(function (): void {
    Route::get('/gio-hang', [CartController::class, 'index'])
        ->name('cart.index');

    Route::post('/gio-hang', [CartController::class, 'add'])
        ->name('cart.add');

    Route::patch('/gio-hang/{variant}', [CartController::class, 'update'])
        ->name('cart.update');

    Route::delete('/gio-hang/{variant}', [CartController::class, 'remove'])
        ->whereNumber('variant')
        ->name('cart.remove');

    Route::middleware('auth')->group(function (): void {
        Route::get('/thanh-toan', [CheckoutController::class, 'create'])
            ->name('checkout.create');

        Route::post('/thanh-toan', [CheckoutController::class, 'store'])
            ->name('checkout.store');
    });
});

Route::get('/don-hang/{order}', [OrderController::class, 'show'])
    ->middleware('auth')
    ->name('orders.show');
