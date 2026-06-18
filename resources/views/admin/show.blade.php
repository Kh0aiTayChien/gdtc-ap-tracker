@extends('layouts.app')
@section('title', $record->ap_name.' - GDTC')
@section('content')
@include('admin.partials.nav')
<div class="mx-auto max-w-4xl">
    <div class="flex flex-wrap items-start justify-between gap-4"><div><div class="text-sm font-bold text-blue-700">{{ $record->team?->name ?? 'Không có tổ' }}</div><h1 class="text-4xl font-black">{{ $record->ap_name }}</h1><div class="mt-2"><span class="badge {{ $record->status }}">{{ $record->status === 'installed' ? 'Đã lắp xong' : $record->issue_reason }}</span></div></div><div class="flex gap-2"><a class="btn-ghost" href="{{ route('admin.records.edit', $record) }}">Sửa</a><form method="POST" action="{{ route('admin.records.destroy', $record) }}" onsubmit="return confirm('Xóa bản ghi này?')">@csrf @method('DELETE')<button class="btn-danger" type="submit">Xóa</button></form></div></div>
    @if($record->issue_note)<div class="mt-5 rounded-2xl bg-amber-50 p-4"><strong>Ghi chú:</strong> {{ $record->issue_note }}</div>@endif
    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        @foreach ([['location_photo','Vị trí lắp đặt'],['mac_photo','Tem MAC + Serial'],['cable_photo','Nhãn dây AP'],['issue_photo','Hiện trường sự cố']] as [$field,$label])
            @if($record->{$field})<figure class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200"><img class="h-72 w-full object-cover" src="{{ Storage::url($record->{$field}) }}" alt="{{ $label }}"><figcaption class="p-3 font-bold">{{ $label }}</figcaption></figure>@endif
        @endforeach
    </div>
    <div class="mt-5 text-sm text-slate-500">Tạo lúc {{ $record->created_at->format('d/m/Y H:i:s') }} · Cập nhật {{ $record->updated_at->format('d/m/Y H:i:s') }}</div>
</div>
@endsection
