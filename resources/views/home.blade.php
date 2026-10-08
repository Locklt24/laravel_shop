@extends('layouts.app')

@section('title', 'Trang chủ')

@section('content')
    <h1>Chào mừng đến với Shop Laravel</h1>
    <p>Website bán hàng được xây dựng bằng Laravel và Blade.</p>
    
        <p>Bạn chưa đăng nhập. <a href="{{ route('login') }}">Đăng nhập</a> hoặc <a href="{{ route('register') }}">Đăng ký</a></p>
    

@endsection