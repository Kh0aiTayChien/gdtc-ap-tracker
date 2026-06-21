<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveApRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $record = $this->route('record');
        $blocked = $this->input('status') === 'blocked';

        return [
            'floor' => ['required', Rule::in(array_merge(['G'], array_map(fn ($n) => "T{$n}", range(1, 24))))],
            'ap_no' => [
                'required', 'integer', 'min:1', 'max:9999',
                Rule::unique('ap_records')->where(fn ($query) => $query->where('floor', $this->input('floor')))->ignore($record?->id),
            ],
            'status' => ['required', Rule::in(['installed', 'blocked'])],
            'record_time' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'location_photo' => ['nullable', 'image', 'max:12288'],
            'mac_photo' => ['nullable', 'image', 'max:12288'],
            'cable_photo' => ['nullable', 'image', 'max:12288'],
            'issue_reason' => [Rule::requiredIf($blocked), 'nullable', Rule::in([
                'Chưa tìm thấy dây', 'Không có nguồn', 'Không tiếp cận được vị trí',
                'Sai vị trí trên bản vẽ', 'Thiếu vật tư', 'Trần/vị trí chưa thi công được', 'Khác',
            ])],
            'issue_note' => ['nullable', 'string', 'max:2000'],
            'issue_photo' => ['nullable', 'image', 'max:12288'],
        ];
    }

    public function messages(): array
    {
        return [
            'ap_no.unique' => 'AP này đã tồn tại.',
            '*.required' => 'Vui lòng nhập/chụp đầy đủ trường này.',
            '*.image' => 'Tệp phải là ảnh hợp lệ.',
            '*.max' => 'Ảnh không được vượt quá 12 MB.',
        ];
    }
}
