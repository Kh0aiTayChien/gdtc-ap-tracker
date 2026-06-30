@extends('layouts.app')
@section('title', 'Cấu hình tầng - GDTC')
@section('header-actions')<a class="btn-ghost !min-h-0 !px-3 !py-2 text-sm" href="{{ route('admin.dashboard') }}">Tổng quan</a>@endsection
@section('content')
@include('admin.partials.nav')

<div class="flex flex-wrap items-end justify-between gap-3">
    <div>
        <h1 class="text-3xl font-black">Cấu hình tầng</h1>
        <p class="mt-1 text-slate-500">Đặt tổng số AP mục tiêu của từng tầng. Dữ liệu AP đã nhập vẫn được giữ nguyên.</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.floor-config.update') }}" class="mt-6 space-y-6">
    @csrf
    @method('PUT')

    <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="grid grid-cols-[1fr_1fr_1fr_1fr] bg-slate-50 px-4 py-3 text-xs font-black uppercase text-slate-500">
            <div>Tầng</div>
            <div>Đã nhập</div>
            <div>Đã lắp</div>
            <div>Tổng AP mục tiêu</div>
        </div>
        <div class="divide-y divide-slate-100">
            @foreach ($floors as $index => $floor)
                <div class="grid items-center gap-3 px-4 py-3 sm:grid-cols-[1fr_1fr_1fr_1fr]">
                    <div class="font-black text-blue-800">{{ $floor->floor }}</div>
                    <div class="text-sm font-bold text-slate-600">{{ $floor->recorded_total }} bản ghi</div>
                    <div class="text-sm font-bold text-emerald-700">{{ $floor->installed }} đã lắp</div>
                    <label class="block">
                        <input type="hidden" name="floors[{{ $index }}][floor]" value="{{ $floor->floor }}">
                        <input class="field max-w-40 text-lg font-black" name="floors[{{ $index }}][target_ap_count]" type="number" min="0" max="9999" value="{{ $floor->total }}">
                    </label>
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="section-title">Thêm tầng mới</h2>
        <div class="mt-4 grid gap-3 sm:grid-cols-[1fr_1fr_auto]">
            <label class="block">
                <span class="label">Tên tầng</span>
                <input class="field text-lg font-black uppercase" name="new_floor" placeholder="VD: T25">
            </label>
            <label class="block">
                <span class="label">Tổng AP mục tiêu</span>
                <input class="field text-lg font-black" name="new_target_ap_count" type="number" min="0" max="9999" value="0">
            </label>
            <div class="flex items-end">
                <button class="btn-primary w-full" type="submit">Lưu cấu hình</button>
            </div>
        </div>
    </section>

    <div class="sticky bottom-3 flex justify-end">
        <button class="btn-primary shadow-lg" type="submit">Lưu thay đổi</button>
    </div>
</form>
@endsection
