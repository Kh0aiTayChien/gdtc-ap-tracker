@extends('layouts.app')
@section('title', $team->name.' - GDTC AP Tracker')
@section('content')
<div class="mx-auto max-w-md pt-8">
    <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="text-sm font-bold uppercase tracking-wider text-blue-700">Đăng nhập tổ thi công</div>
        <h1 class="mt-2 text-3xl font-black">{{ $team->name }}</h1>
        <p class="mt-2 text-slate-500">Nhập mã một lần để bắt đầu ghi nhận AP.</p>
        <form method="POST" action="{{ route('team.login', $team) }}" class="mt-6 space-y-4">
            @csrf
            <label class="block font-bold" for="access_code">Mã truy cập</label>
            <input class="field text-center text-xl tracking-widest" id="access_code" name="access_code" type="password" required autofocus autocomplete="current-password">
            <button class="btn-primary w-full" type="submit">Tiếp tục</button>
        </form>
    </div>
</div>
@endsection
