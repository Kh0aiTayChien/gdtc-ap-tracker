@extends('layouts.app')
@section('title', 'Quản trị - GDTC AP Tracker')
@section('content')
<div class="mx-auto max-w-md pt-8">
    <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="text-sm font-bold uppercase tracking-wider text-blue-700">Khu vực quản trị</div>
        <h1 class="mt-2 text-3xl font-black">Đăng nhập</h1>
        <form method="POST" action="{{ route('admin.login.submit') }}" class="mt-6 space-y-4">
            @csrf
            <label class="block font-bold" for="access_code">Mã quản trị</label>
            <input class="field text-center text-xl tracking-widest" id="access_code" name="access_code" type="password" required autofocus>
            <button class="btn-primary w-full" type="submit">Mở bảng điều khiển</button>
        </form>
    </div>
</div>
@endsection
