@extends('layouts.app')
@section('title', 'Hôm nay - '.$team->name)
@section('header-actions')<a class="btn-primary !min-h-0 !px-3 !py-2 text-sm" href="{{ route('team.records.create', $team) }}">+ Thêm AP</a>@endsection
@section('content')
<div class="mx-auto max-w-2xl">
    <div class="mb-5 flex items-end justify-between"><div><div class="text-sm font-bold text-blue-700">{{ $team->name }}</div><h1 class="text-3xl font-black">Hôm nay</h1></div><div class="text-sm text-slate-500">{{ now()->format('d/m/Y') }}</div></div>
    <div class="space-y-3">
        @forelse ($records as $record)
            <article class="flex items-center gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <div class="h-12 w-2 rounded-full {{ $record->status === 'installed' ? 'bg-emerald-500' : 'bg-amber-500' }}"></div>
                <div class="min-w-0 flex-1"><h2 class="text-xl font-black">{{ $record->ap_name }}</h2><p class="truncate text-sm {{ $record->status === 'installed' ? 'text-emerald-700' : 'text-amber-700' }}">{{ $record->status === 'installed' ? 'Đã lắp' : $record->issue_reason }}</p></div>
                <div class="text-right"><time class="block font-bold">{{ $record->created_at->format('H:i') }}</time><a class="mt-1 inline-block font-bold text-blue-700" href="{{ route('team.records.edit', [$team, $record]) }}">Sửa</a></div>
            </article>
        @empty
            <div class="rounded-3xl border-2 border-dashed border-slate-300 p-10 text-center text-slate-500">Chưa có AP nào hôm nay.</div>
        @endforelse
    </div>
</div>
@endsection
