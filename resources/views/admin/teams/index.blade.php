@extends('layouts.app')
@section('title', 'Nhóm thi công - GDTC')
@section('header-actions')<a class="btn-primary !min-h-0 !px-3 !py-2 text-sm" href="{{ route('admin.teams.create') }}">+ Thêm nhóm</a>@endsection
@section('content')
@include('admin.partials.nav')

<div class="flex flex-wrap items-end justify-between gap-3">
    <div>
        <h1 class="text-3xl font-black">Nhóm thi công</h1>
        <p class="mt-1 text-slate-500">Tạo thêm nhóm khi cần và cấp link truy cập riêng cho từng nhóm.</p>
    </div>
</div>

<div class="mt-6 overflow-x-auto rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
    <table class="w-full min-w-[760px] text-left">
        <thead class="bg-slate-50 text-xs uppercase text-slate-500">
            <tr>
                <th class="p-4">Nhóm</th>
                <th class="p-4">Link truy cập</th>
                <th class="p-4">Mã</th>
                <th class="p-4">Đã lắp</th>
                <th class="p-4">Sự cố</th>
                <th class="p-4 text-right">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($teams as $team)
                <tr class="border-t border-slate-100">
                    <td class="p-4 font-black">{{ $team->name }}</td>
                    <td class="p-4"><a class="font-bold text-blue-700" href="{{ route('team.home', $team) }}">{{ route('team.home', $team) }}</a></td>
                    <td class="p-4 font-mono text-sm">{{ $team->access_code }}</td>
                    <td class="p-4 font-bold text-emerald-700">{{ $team->installed }}</td>
                    <td class="p-4 font-bold text-amber-700">{{ $team->blocked }}</td>
                    <td class="p-4 text-right"><a class="font-bold text-blue-700" href="{{ route('admin.teams.edit', $team) }}">Sửa</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="p-10 text-center text-slate-500">Chưa có nhóm.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
