@extends('layouts.app')

@section('title', 'Đăng ký')

@section('content')
    <h1>Đăng ký tài khoản</h1>

    <form action="{{ route('register.store') }}" method="POST">
        @csrf

        <input name="name" placeholder="Họ tên" value="{{ old('name') }}">
        @error('name') <p style="color:red">{{ $message }}</p> @enderror

        <br><br>

        <input type="email" name="email" placeholder="Email" value="{{ old('email') }}">
        @error('email') <p style="color:red">{{ $message }}</p> @enderror

        <br><br>

        <input type="password" name="password" placeholder="Mật khẩu">
        @error('password') <p style="color:red">{{ $message }}</p> @enderror

        <br><br>

        <input type="password" name="password_confirmation" placeholder="Nhập lại mật khẩu">

        <br><br>

        <button type="submit">Đăng ký</button>
    </form>
@endsection