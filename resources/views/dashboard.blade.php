@extends('layouts.app')

@section('title', 'Tài khoản')

@section('content')
    <h1>Xin chào, {{ auth()->user()->name }}!</h1>
    <p>Bạn đã đăng nhập thành công.</p>
@endsection