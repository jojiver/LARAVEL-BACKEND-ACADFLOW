<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subjectId' => ['sometimes', 'required', 'integer', 'exists:subjects,id'],
            'day' => ['sometimes', 'required', Rule::in(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'])],
            'startTime' => ['sometimes', 'required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'endTime' => ['sometimes', 'required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'room' => ['sometimes', 'nullable', 'string', 'max:80'],
        ];
    }
}
