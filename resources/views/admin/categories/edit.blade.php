@extends('layouts.app')
@section('title', 'Sửa danh mục')
@section('content')
    <section class="admin-dashboard-page">
        <header class="admin-page-heading">
            <div><span class="eyebrow">Danh mục</span><h1>Sửa danh mục</h1></div>
            <a class="admin-secondary-button" href="{{ route('admin.categories.index') }}">← Quay lại</a>
        </header>
        @include('partials.admin-nav')
        <section class="admin-form-panel">
            <form method="POST" action="{{ route('admin.categories.update', $category) }}">
                @csrf @method('PUT')
                <label for="name">Tên danh mục</label>
                <input id="name" name="name" value="{{ old('name', $category->name) }}" required>
                @error('name') <span class="admin-form-error">{{ $message }}</span> @enderror
                <button class="admin-primary-button" type="submit">Cập nhật danh mục</button>
            </form>
        </section>
    </section>
@endsection
