@php
    $isAdminView = request()->routeIs('admin.*');
    $routeTeam = request()->route('team');
@endphp
@extends('layouts.app')
@section('title', $title.' - GDTC')
@section('header-actions')
    <div class="flex gap-2">
        <a class="btn-ghost !min-h-0 !px-3 !py-2 text-sm" href="{{ $backUrl }}">{{ $backLabel }}</a>
        <form method="POST" action="{{ $isAdminView ? route('admin.logout') : route('team.logout', $routeTeam) }}">
            @csrf
            <button class="btn-danger !min-h-0 !px-3 !py-2 text-sm" type="submit">Thoát</button>
        </form>
    </div>
@endsection
@section('content')
@if ($isAdminView)
    @include('admin.partials.nav')
@endif

<div class="flex flex-wrap items-end justify-between gap-3">
    <div>
        <h1 class="text-3xl font-black">{{ $title }}</h1>
        <p class="mt-1 text-slate-500">Theo dõi từng tầng, từng AP, nhóm thi công và trạng thái lắp đặt.</p>
    </div>
    @if (! $isAdminView && $routeTeam)
        <a class="btn-primary !min-h-0 !px-4 !py-3 text-sm" href="{{ route('team.records.create', $routeTeam) }}">+ Thêm AP</a>
    @endif
</div>

@if ($floors->isNotEmpty())
    <section class="sticky top-[73px] z-20 -mx-4 mt-5 border-y border-slate-200 bg-white/95 px-4 py-3 shadow-sm backdrop-blur sm:hidden">
        <label class="block">
            <span class="label mb-1">Tầng mục tiêu</span>
            <select class="field min-h-14 text-lg font-black" onchange="if (this.value) document.querySelector(this.value)?.scrollIntoView({ behavior: 'smooth', block: 'start' })">
                @foreach ($floors as $floor)
                    <option value="#floor-{{ $floor->floor }}">
                        {{ $floor->floor }} · {{ $floor->installed }}/{{ $floor->total }} · {{ $floor->percent }}%
                    </option>
                @endforeach
            </select>
        </label>
    </section>
@endif

<section class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
    @forelse ($floors as $floor)
        <a class="stat-card progress-card block hover:ring-blue-200" href="#floor-{{ $floor->floor }}">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-2xl font-black text-blue-800">{{ $floor->floor }}</h2>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-bold">{{ $floor->installed }}/{{ $floor->total }} AP</span>
            </div>
            <div class="progress-track mt-4"><div class="progress-fill" style="width: {{ min(100, $floor->percent) }}%"></div></div>
            <div class="mt-4 grid grid-cols-2 gap-2">
                <div class="metric installed"><strong>{{ $floor->installed }}</strong><span>Đã lắp</span></div>
                <div class="metric blocked"><strong>{{ $floor->remaining }}</strong><span>Còn thiếu</span></div>
            </div>
        </a>
    @empty
        <div class="rounded-3xl border-2 border-dashed border-slate-300 p-10 text-center text-slate-500 sm:col-span-2 lg:col-span-4">Chưa có dữ liệu AP.</div>
    @endforelse
</section>

<div class="mt-7 space-y-6">
    @foreach ($floors as $floor)
        <section id="floor-{{ $floor->floor }}" class="scroll-mt-36 sm:scroll-mt-24">
            <div class="mb-3 flex items-center justify-between gap-3">
                <div>
                    <h2 class="section-title">Tầng {{ $floor->floor }}</h2>
                    <div class="mt-1 text-sm font-bold text-slate-500">{{ $floor->installed }}/{{ $floor->total }} AP · {{ $floor->percent }}% hoàn thành · còn {{ $floor->remaining }}</div>
                </div>
                <div class="grid min-w-48 grid-cols-3 gap-2 text-center">
                    <div class="rounded-xl bg-emerald-50 px-3 py-2 text-emerald-700"><div class="text-xl font-black">{{ $floor->installed }}</div><div class="text-[11px] font-bold uppercase">Đã lắp</div></div>
                    <div class="rounded-xl bg-amber-50 px-3 py-2 text-amber-700"><div class="text-xl font-black">{{ $floor->blocked }}</div><div class="text-[11px] font-bold uppercase">Sự cố</div></div>
                    <div class="rounded-xl bg-slate-50 px-3 py-2 text-slate-700"><div class="text-xl font-black">{{ $floor->remaining }}</div><div class="text-[11px] font-bold uppercase">Còn</div></div>
                </div>
            </div>
            <div class="progress-track mb-3"><div class="progress-fill" style="width: {{ min(100, $floor->percent) }}%"></div></div>

            <div class="space-y-3 sm:hidden">
                @foreach ($floor->records as $record)
                    <article class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="text-xl font-black leading-tight">{{ $record->ap_name }}</h3>
                                <div class="mt-1 text-sm font-bold text-slate-500">{{ $record->team?->name ?? 'Chưa có nhóm' }}</div>
                            </div>
                            <span class="badge {{ $record->status }} shrink-0">{{ $record->status === 'installed' ? 'Đã lắp' : 'Sự cố' }}</span>
                        </div>

                        @if ($record->status === 'blocked')
                            <div class="mt-3 rounded-xl bg-amber-50 px-3 py-2 text-sm font-bold text-amber-800">{{ $record->issue_reason }}</div>
                        @endif

                        <div class="mt-3 flex items-center justify-between gap-3">
                            <div class="text-sm font-bold text-slate-500">{{ $record->work_date?->format('d/m/Y') ?? 'Chưa có ngày' }}</div>
                            @if ($isAdminView)
                                <a class="btn-ghost !min-h-0 !px-4 !py-2 text-sm" href="{{ route('admin.records.show', $record) }}">Xem</a>
                            @elseif ((int) session('team_id') === (int) $record->team_id)
                                <a class="btn-primary !min-h-0 !px-4 !py-2 text-sm" href="{{ route('team.records.edit', [$routeTeam, $record]) }}">Sửa</a>
                            @endif
                        </div>

                        @if ($record->issue_note)
                            <div class="mt-3 text-sm text-slate-600">{{ $record->issue_note }}</div>
                        @endif
                    </article>
                @endforeach
            </div>

            <div class="hidden overflow-x-auto rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 sm:block">
                <table class="w-full min-w-[720px] text-left">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="p-4">AP</th>
                            <th class="p-4">Nhóm</th>
                            <th class="p-4">Trạng thái</th>
                            <th class="p-4">Ngày thi công</th>
                            <th class="p-4">Ghi chú</th>
                            <th class="p-4 text-right">Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($floor->records as $record)
                            <tr class="border-t border-slate-100">
                                <td class="p-4 font-black">{{ $record->ap_name }}</td>
                                <td class="p-4">{{ $record->team?->name ?? 'Chưa có nhóm' }}</td>
                                <td class="p-4"><span class="badge {{ $record->status }}">{{ $record->status === 'installed' ? 'Đã lắp' : $record->issue_reason }}</span></td>
                                <td class="p-4 text-sm font-bold">{{ $record->work_date?->format('d/m/Y') ?? 'Chưa có ngày' }}</td>
                                <td class="p-4 text-sm text-slate-600">{{ $record->issue_note ?: '-' }}</td>
                                <td class="p-4 text-right">
                                    @if ($isAdminView)
                                        <a class="font-bold text-blue-700" href="{{ route('admin.records.show', $record) }}">Xem</a>
                                    @elseif ((int) session('team_id') === (int) $record->team_id)
                                        <a class="font-bold text-blue-700" href="{{ route('team.records.edit', [$routeTeam, $record]) }}">Sửa</a>
                                    @else
                                        <span class="text-sm text-slate-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endforeach
</div>
@endsection
