@php
    $isEdit = $team->exists;
    $action = $isEdit ? route('admin.teams.update', $team) : route('admin.teams.store');
@endphp
@extends('layouts.app')
@section('title', ($isEdit ? 'Sửa nhóm' : 'Thêm nhóm').' - GDTC')
@section('header-actions')<a class="btn-ghost !min-h-0 !px-3 !py-2 text-sm" href="{{ route('admin.teams.index') }}">Danh sách</a>@endsection
@section('content')
@include('admin.partials.nav')

<form method="POST" action="{{ $action }}" class="mx-auto max-w-2xl space-y-5">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <section class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h1 class="text-3xl font-black">{{ $isEdit ? 'Sửa nhóm' : 'Thêm nhóm' }}</h1>
        <div class="mt-5 space-y-4">
            <label class="block"><span class="label">Tên nhóm</span>
                <input class="field" name="name" value="{{ old('name', $team->name) }}" required autofocus>
            </label>
            <label class="block"><span class="label">Đường dẫn đăng nhập</span>
                <input class="field font-mono" name="login_slug" value="{{ old('login_slug', $team->login_slug) }}" placeholder="Tự tạo nếu bỏ trống">
            </label>
            <label class="block"><span class="label">Mã truy cập</span>
                <input class="field font-mono" name="access_code" value="{{ old('access_code', $team->access_code) }}" required>
            </label>
        </div>
    </section>

    <button class="btn-primary w-full text-lg" type="submit">{{ $isEdit ? 'Lưu thay đổi' : 'Tạo nhóm' }}</button>
</form>
@endsection
