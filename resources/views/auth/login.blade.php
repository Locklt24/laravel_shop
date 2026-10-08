@extends('layouts.app')

@section('title', 'Đăng nhập')

@section('content')
    <h1>Đăng nhập</h1>

    <form action="{{ route('login.store') }}" method="POST">
        @csrf

        <input type="email" name="email" placeholder="Email" value="{{ old('email') }}">
        @error('email') <p style="color:red">{{ $message }}</p> @enderror

        <br><br>

        <input type="password" name="password" placeholder="Mật khẩu">
        @error('password') <p style="color:red">{{ $message }}</p> @enderror

        <br><br>

        <button type="submit">Đăng nhập</button>
    </form>
@endsection