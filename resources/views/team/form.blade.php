@php
    $isEdit = $record->exists;
    $isAdmin = $admin ?? false;
    $action = $isAdmin ? route('admin.records.update', $record) : ($isEdit ? route('team.records.update', [$team, $record]) : route('team.records.store', $team));
    $floor = old('floor', $record->floor ?: 'G');
    $apNo = old('ap_no', $record->ap_no ?: 1);
    $status = old('status', $record->status ?: 'installed');
    $workDate = old('work_date', optional($record->work_date)->format('Y-m-d') ?: now()->format('Y-m-d'));
    $recordTime = old('record_time', optional($record->created_at)->format('Y-m-d\TH:i') ?: now()->format('Y-m-d\TH:i'));
@endphp
@extends('layouts.app')
@section('title', ($isEdit ? 'Sửa AP' : 'Thêm AP').' - GDTC')
@section('header-actions')
    <a class="btn-ghost !min-h-0 !px-3 !py-2 text-sm" href="{{ $isAdmin ? route('admin.records.index') : route('team.today', $team) }}">Danh sách</a>
@endsection
@section('content')
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" x-data="apForm(@js($floor), @js((int) $apNo), @js($status))" class="mx-auto max-w-2xl space-y-5">
    @csrf
    @if ($isEdit) @method('PUT') @endif
    <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <div class="grid grid-cols-2 gap-3">
            <label class="block"><span class="label">Tầng</span>
                <select class="field text-lg font-bold" name="floor" x-model="floor">
                    <option value="G">G</option>
                    @foreach (range(1, 24) as $n)<option value="T{{ $n }}">T{{ $n }}</option>@endforeach
                </select>
            </label>
            <div><span class="label">Số AP</span>
                <div class="flex gap-2">
                    <button class="stepper" type="button" @click="apNo = Math.max(1, apNo - 1)">−</button>
                    <input class="field min-w-0 text-center text-lg font-bold" name="ap_no" type="number" inputmode="numeric" min="1" max="9999" x-model.number="apNo">
                    <button class="stepper" type="button" @click="apNo++">+</button>
                </div>
            </div>
        </div>
        <div class="mt-5 rounded-2xl bg-blue-50 p-4 text-center text-3xl font-black text-blue-800" x-text="`${floor}-AP${apNo || 1}`"></div>
        <div class="mt-5 grid gap-3 sm:grid-cols-2">
            <label class="block"><span class="label">Ngày thi công</span>
                <input class="field" name="work_date" type="date" value="{{ $workDate }}" required>
            </label>
            <label class="block"><span class="label">Thời gian tạo bản ghi</span>
                <input class="field" name="record_time" type="datetime-local" value="{{ $recordTime }}">
            </label>
        </div>
    </div>

    <fieldset class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <legend class="px-2 text-lg font-black">Trạng thái</legend>
        <div class="space-y-3">
            <label class="status-option" :class="status === 'installed' && 'active-installed'">
                <input class="h-6 w-6 accent-emerald-600" type="radio" name="status" value="installed" x-model="status">
                <span><strong class="block text-lg">Đã lắp xong</strong><small class="text-slate-500">Đã hoàn thiện và chụp đủ bằng chứng</small></span>
            </label>
            <label class="status-option" :class="status === 'blocked' && 'active-blocked'">
                <input class="h-6 w-6 accent-amber-600" type="radio" name="status" value="blocked" x-model="status">
                <span><strong class="block text-lg">Chưa lắp được / Có sự cố</strong><small class="text-slate-500">Ghi nhận lý do và hiện trường</small></span>
            </label>
        </div>
    </fieldset>

    <section x-show="status === 'installed'" x-cloak class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="text-lg font-black">Ảnh nghiệm thu <span class="text-sm font-bold text-slate-500">(không bắt buộc)</span></h2>
        <div class="mt-4 space-y-5">
            @foreach ([['location_photo','Ảnh vị trí lắp đặt'],['mac_photo','Ảnh tem MAC + Serial'],['cable_photo','Ảnh nhãn dây AP']] as [$field,$label])
                <label class="photo-field"><span class="label">{{ $label }}</span>
                    @if ($record->{$field})<img class="mb-3 h-40 w-full rounded-xl object-cover" src="{{ Storage::url($record->{$field}) }}" alt="{{ $label }}">@endif
                    <input name="{{ $field }}" type="file" accept="image/*" capture="environment" class="block w-full text-sm file:mr-3 file:rounded-xl file:border-0 file:bg-blue-700 file:px-4 file:py-3 file:font-bold file:text-white">
                    @if ($isEdit)<small class="text-slate-500">Không chọn ảnh để giữ ảnh hiện tại.</small>@endif
                </label>
            @endforeach
        </div>
    </section>

    <section x-show="status === 'blocked'" x-cloak class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="text-lg font-black text-amber-800">Thông tin sự cố</h2>
        <div class="mt-4 space-y-5">
            <label class="block"><span class="label">Lý do sự cố <span class="text-red-600">*</span></span>
                <select class="field" name="issue_reason" :required="status === 'blocked'">
                    <option value="">Chọn lý do</option>
                    @foreach (['Chưa tìm thấy dây','Không có nguồn','Không tiếp cận được vị trí','Sai vị trí trên bản vẽ','Thiếu vật tư','Trần/vị trí chưa thi công được','Khác'] as $reason)
                        <option value="{{ $reason }}" @selected(old('issue_reason', $record->issue_reason) === $reason)>{{ $reason }}</option>
                    @endforeach
                </select>
            </label>
            <label class="photo-field"><span class="label">Ảnh hiện trường sự cố <span class="text-sm font-bold text-slate-500">(không bắt buộc)</span></span>
                @if ($record->issue_photo)<img class="mb-3 h-40 w-full rounded-xl object-cover" src="{{ Storage::url($record->issue_photo) }}" alt="Ảnh sự cố">@endif
                <input name="issue_photo" type="file" accept="image/*" capture="environment" class="block w-full text-sm file:mr-3 file:rounded-xl file:border-0 file:bg-amber-600 file:px-4 file:py-3 file:font-bold file:text-white">
            </label>
            <label class="block"><span class="label">Ghi chú</span><textarea class="field min-h-28" name="issue_note" maxlength="2000" placeholder="Thông tin bổ sung (không bắt buộc)">{{ old('issue_note', $record->issue_note) }}</textarea></label>
        </div>
    </section>

    <div class="fixed inset-x-0 bottom-0 z-20 border-t border-slate-200 bg-white/95 p-3 backdrop-blur">
        <div class="mx-auto max-w-2xl"><button class="btn-primary w-full text-lg" type="submit">{{ $isEdit ? 'Lưu thay đổi' : 'Lưu AP' }}</button></div>
    </div>
</form>
@endsection
