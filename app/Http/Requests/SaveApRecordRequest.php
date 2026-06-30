<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

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
            'floor' => ['required', 'regex:/^(G|T[1-9][0-9]{0,2})$/'],
            'ap_no' => [
                'required', 'integer', 'min:1', 'max:9999',
                Rule::unique('ap_records')->where(fn ($query) => $query->where('floor', $this->input('floor')))->ignore($record?->id),
            ],
            'status' => ['required', Rule::in(['installed', 'blocked'])],
            'work_date' => ['required', 'date'],
            'record_time' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'location_photo' => ['nullable', $this->photoRule()],
            'mac_photo' => ['nullable', $this->photoRule()],
            'cable_photo' => ['nullable', $this->photoRule()],
            'issue_reason' => [Rule::requiredIf($blocked), 'nullable', Rule::in([
                'Chưa tìm thấy dây', 'Không có nguồn', 'Không tiếp cận được vị trí',
                'Sai vị trí trên bản vẽ', 'Thiếu vật tư', 'Trần/vị trí chưa thi công được', 'Khác',
            ])],
            'issue_note' => ['nullable', 'string', 'max:2000'],
            'issue_photo' => ['nullable', $this->photoRule()],
        ];
    }

    public function messages(): array
    {
        return [
            'ap_no.unique' => 'AP này đã tồn tại.',
            '*.required' => 'Vui lòng nhập/chụp đầy đủ trường này.',
            '*.file' => 'Tệp tải lên không hợp lệ.',
            '*.mimes' => 'Tệp phải là ảnh JPG, PNG, WEBP, GIF, HEIC hoặc HEIF.',
            '*.max' => 'Ảnh không được vượt quá 20 MB.',
        ];
    }

    private function photoRule(): File
    {
        return File::types(['jpg', 'jpeg', 'png', 'webp', 'gif', 'heic', 'heif'])->max(20 * 1024);
    }
}
