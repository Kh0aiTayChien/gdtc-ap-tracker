@extends('layouts.app')
@section('title', 'Bảng điều khiển - GDTC')
@section('header-actions')<span id="realtime-indicator" class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-500">Realtime Disconnected</span>@endsection
@section('content')
@include('admin.partials.nav')
<div data-admin-dashboard>
    <div id="realtime-feed" class="mb-6 space-y-2"></div>
    <h1 class="text-3xl font-black">Tiến độ thi công</h1>
    <p class="mt-1 text-slate-500">Số liệu cập nhật theo bản ghi hiện tại.</p>

    <section class="mt-7"><h2 class="section-title">Theo tổ</h2>
        <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($byTeam as $team)
                <article class="stat-card"><h3 class="text-lg font-black">{{ $team->name }}</h3><div class="mt-4 grid grid-cols-2 gap-2"><div class="metric installed"><strong>{{ $team->installed }}</strong><span>Đã lắp</span></div><div class="metric blocked"><strong>{{ $team->blocked }}</strong><span>Sự cố</span></div></div></article>
            @endforeach
        </div>
    </section>

    <section class="mt-7"><h2 class="section-title">Theo tầng</h2>
        <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($byFloor as $floor)
                <article class="stat-card"><div class="flex items-center justify-between"><h3 class="text-2xl font-black text-blue-800">{{ $floor->floor }}</h3><span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-bold">{{ $floor->total }} AP</span></div><div class="mt-4 flex justify-between text-sm"><span class="font-bold text-emerald-700">Đã lắp: {{ $floor->installed }}</span><span class="font-bold text-amber-700">Sự cố: {{ $floor->blocked }}</span></div></article>
            @empty <div class="text-slate-500">Chưa có dữ liệu.</div> @endforelse
        </div>
    </section>

    <section class="mt-7"><h2 class="section-title">Mới nhận</h2>
        <div class="mt-3 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            @forelse ($latest as $record)
                <a href="{{ route('admin.records.show', $record) }}" class="flex items-center gap-3 border-b border-slate-100 p-4 last:border-0 hover:bg-slate-50"><span class="h-3 w-3 rounded-full {{ $record->status === 'installed' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span><strong class="flex-1">{{ $record->ap_name }}</strong><span class="text-sm text-slate-500">{{ $record->team?->name }} · {{ $record->created_at->format('H:i') }}</span></a>
            @empty <div class="p-6 text-slate-500">Chưa có dữ liệu.</div> @endforelse
        </div>
    </section>
</div>
@endsection
