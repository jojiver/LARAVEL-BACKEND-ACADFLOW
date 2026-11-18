<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:'.config('schedule_import.max_size_kb', 10240),
            ],
        ];
    }
}
