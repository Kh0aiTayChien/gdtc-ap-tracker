@extends('layouts.app')
@section('title', 'Bảng điều khiển - GDTC')
@section('header-actions')<span id="realtime-indicator" class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-500">Realtime Disconnected</span>@endsection
@section('content')
@include('admin.partials.nav')
<div data-admin-dashboard>
    <div id="realtime-feed" class="mb-6 space-y-2"></div>
    <h1 class="text-3xl font-black">Tiến độ thi công</h1>
    <p class="mt-1 text-slate-500">Số liệu cập nhật theo cấu hình tổng AP của từng tầng.</p>

    <section class="mt-7 grid gap-3 sm:grid-cols-4">
        <article class="stat-card progress-glow"><div class="text-sm font-bold text-slate-500">Tổng AP mục tiêu</div><div class="mt-2 text-4xl font-black text-blue-800">{{ $configuredApTotal }}</div></article>
        <article class="stat-card"><div class="text-sm font-bold text-emerald-700">Tổng đã lắp</div><div class="mt-2 text-4xl font-black text-emerald-700">{{ $summary->installed ?? 0 }}</div></article>
        <article class="stat-card"><div class="text-sm font-bold text-amber-700">Cần xử lý</div><div class="mt-2 text-4xl font-black text-amber-700">{{ $summary->blocked ?? 0 }}</div></article>
        <article class="stat-card">
            <div class="text-sm font-bold text-slate-500">Hoàn thành</div>
            <div class="mt-2 text-4xl font-black text-blue-800">{{ $overallPercent }}%</div>
            <div class="progress-track mt-4"><div class="progress-fill" style="width: {{ min(100, $overallPercent) }}%"></div></div>
        </article>
    </section>

    <section class="mt-7"><h2 class="section-title">Theo tuần</h2>
        <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($weeklyProgress as $week)
                <article class="stat-card progress-card">
                    <div class="text-sm font-bold text-slate-500">{{ \Illuminate\Support\Carbon::parse($week->week_start)->format('d/m') }} - {{ \Illuminate\Support\Carbon::parse($week->week_end)->format('d/m') }}</div>
                    <div class="mt-2 flex items-end justify-between gap-3">
                        <div><div class="text-3xl font-black text-blue-800">{{ $week->percent }}%</div><div class="text-xs font-bold uppercase text-slate-500">của mục tiêu</div></div>
                        <div class="text-right text-sm font-bold text-slate-600">{{ $week->installed }} đã lắp<br>{{ $week->blocked }} sự cố</div>
                    </div>
                    <div class="progress-track mt-4"><div class="progress-fill" style="width: {{ min(100, $week->percent) }}%"></div></div>
                </article>
            @empty
                <div class="rounded-2xl border-2 border-dashed border-slate-300 p-6 text-center text-slate-500 sm:col-span-2 lg:col-span-4">Chưa có dữ liệu theo tuần.</div>
            @endforelse
        </div>
    </section>

    <section class="mt-7"><h2 class="section-title">Theo ngày thi công</h2>
        <div class="mt-3 overflow-x-auto rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <table class="w-full min-w-[560px] text-left">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="p-4">Ngày</th><th class="p-4">Đã lắp</th><th class="p-4">Cần xử lý</th><th class="p-4">Tổng</th></tr></thead>
                <tbody>
                    @forelse ($byWorkDate as $day)
                        <tr class="border-t border-slate-100"><td class="p-4 font-black">{{ \Illuminate\Support\Carbon::parse($day->work_date)->format('d/m/Y') }}</td><td class="p-4 font-bold text-emerald-700">{{ $day->installed }}</td><td class="p-4 font-bold text-amber-700">{{ $day->blocked }}</td><td class="p-4 font-black">{{ $day->total }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="p-6 text-center text-slate-500">Chưa có ngày thi công.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="mt-7"><h2 class="section-title">Theo tổ</h2>
        <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($byTeam as $team)
                <article class="stat-card"><h3 class="text-lg font-black">{{ $team->name }}</h3><div class="mt-4 grid grid-cols-2 gap-2"><div class="metric installed"><strong>{{ $team->installed }}</strong><span>Đã lắp</span></div><div class="metric blocked"><strong>{{ $team->blocked }}</strong><span>Sự cố</span></div></div></article>
            @endforeach
        </div>
    </section>

    <section class="mt-7"><h2 class="section-title">Theo tầng</h2>
        <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($floorProgress as $floor)
                <article class="stat-card progress-card">
                    <div class="flex items-center justify-between"><h3 class="text-2xl font-black text-blue-800">{{ $floor->floor }}</h3><span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-bold">{{ $floor->installed }}/{{ $floor->total }} AP</span></div>
                    <div class="progress-track mt-4"><div class="progress-fill" style="width: {{ min(100, $floor->percent) }}%"></div></div>
                    <div class="mt-3 flex justify-between text-sm"><span class="font-bold text-emerald-700">{{ $floor->percent }}%</span><span class="font-bold text-slate-500">Còn {{ $floor->remaining }}</span></div>
                </article>
            @empty <div class="text-slate-500">Chưa có dữ liệu.</div> @endforelse
        </div>
    </section>

    <section class="mt-7"><h2 class="section-title">Mới nhận</h2>
        <div class="mt-3 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            @forelse ($latest as $record)
                <a href="{{ route('admin.records.show', $record) }}" class="flex items-center gap-3 border-b border-slate-100 p-4 last:border-0 hover:bg-slate-50"><span class="h-3 w-3 rounded-full {{ $record->status === 'installed' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span><strong class="flex-1">{{ $record->ap_name }}</strong><span class="text-sm text-slate-500">{{ $record->team?->name }} · {{ $record->work_date?->format('d/m/Y') ?? 'Chưa có ngày' }}</span></a>
            @empty <div class="p-6 text-slate-500">Chưa có dữ liệu.</div> @endforelse
        </div>
    </section>
</div>
@endsection
