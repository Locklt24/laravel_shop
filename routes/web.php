<?php

use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
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
});

Route::middleware('auth')->group(function () {
    Route::get('/tai-khoan', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::post('/dang-xuat', [AuthController::class, 'logout'])
        ->name('logout');

    Route::get('/don-hang-cua-toi', [OrderController::class, 'index'])
        ->name('orders.index');
});
Route::middleware(['auth', 'admin'])->group(function () {
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
