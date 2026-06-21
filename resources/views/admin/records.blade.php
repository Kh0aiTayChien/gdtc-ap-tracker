@extends('layouts.app')
@section('title', 'Bản ghi AP - GDTC')
@section('content')
@include('admin.partials.nav')
<div class="flex flex-wrap items-end justify-between gap-3"><div><h1 class="text-3xl font-black">Bản ghi AP</h1><p class="text-slate-500">Lọc, xem, sửa và xuất dữ liệu.</p></div><a class="btn-primary" href="{{ route('admin.export', request()->query()) }}">Xuất CSV</a></div>
<form method="GET" class="mt-5 grid gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:grid-cols-2 lg:grid-cols-6">
    <input class="field" type="date" name="date" value="{{ request('date') }}" aria-label="Ngày thi công">
    <select class="field" name="floor"><option value="">Mọi tầng</option><option value="G" @selected(request('floor')==='G')>G</option>@foreach(range(1,24) as $n)<option value="T{{ $n }}" @selected(request('floor')==="T{$n}")>T{{ $n }}</option>@endforeach</select>
    <select class="field" name="team"><option value="">Mọi tổ</option>@foreach($teams as $team)<option value="{{ $team->id }}" @selected((string)request('team')===(string)$team->id)>{{ $team->name }}</option>@endforeach</select>
    <select class="field" name="status"><option value="">Mọi trạng thái</option><option value="installed" @selected(request('status')==='installed')>Đã lắp</option><option value="blocked" @selected(request('status')==='blocked')>Sự cố</option></select>
    <input class="field" name="ap_name" value="{{ request('ap_name') }}" placeholder="Tên AP">
    <button class="btn-primary" type="submit">Lọc</button>
</form>
<div class="mt-5 overflow-x-auto rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
    <table class="w-full min-w-[860px] text-left"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="p-4">AP</th><th class="p-4">Tổ</th><th class="p-4">Trạng thái</th><th class="p-4">Ngày thi công</th><th class="p-4">Tạo bản ghi</th><th class="p-4 text-right">Thao tác</th></tr></thead>
    <tbody>@forelse($records as $record)<tr class="border-t border-slate-100"><td class="p-4 font-black">{{ $record->ap_name }}</td><td class="p-4">{{ $record->team?->name ?? '—' }}</td><td class="p-4"><span class="badge {{ $record->status }}">{{ $record->status === 'installed' ? 'Đã lắp' : $record->issue_reason }}</span></td><td class="p-4 text-sm font-bold">{{ $record->work_date?->format('d/m/Y') ?? '—' }}</td><td class="p-4 text-sm">{{ $record->created_at->format('d/m/Y H:i') }}</td><td class="p-4 text-right"><a class="font-bold text-blue-700" href="{{ route('admin.records.show', $record) }}">Xem</a><a class="ml-4 font-bold text-slate-700" href="{{ route('admin.records.edit', $record) }}">Sửa</a></td></tr>@empty<tr><td colspan="6" class="p-10 text-center text-slate-500">Không có dữ liệu phù hợp.</td></tr>@endforelse</tbody></table>
</div>
<div class="mt-5">{{ $records->links() }}</div>
@endsection
