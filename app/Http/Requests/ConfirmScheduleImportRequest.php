<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConfirmScheduleImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'classes' => ['required', 'array', 'min:1'],
            'classes.*.subject' => ['required', 'string', 'max:160'],
            'classes.*.day' => ['required', Rule::in(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'])],
            'classes.*.startTime' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'classes.*.endTime' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'classes.*.room' => ['nullable', 'string', 'max:80'],
            'classes.*.selected' => ['sometimes', 'boolean'],
        ];
    }
}
