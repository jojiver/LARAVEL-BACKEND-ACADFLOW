<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subjectId' => ['required', 'integer', 'exists:subjects,id'],
            'day' => ['required', Rule::in(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'])],
            'startTime' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'endTime' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'room' => ['nullable', 'string', 'max:80'],
        ];
    }
}
