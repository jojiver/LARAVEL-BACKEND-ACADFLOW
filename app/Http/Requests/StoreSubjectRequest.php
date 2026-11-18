<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'instructor' => ['nullable', 'string', 'max:160'],
            'room' => ['nullable', 'string', 'max:80'],
            'schedule' => ['nullable', 'string', 'max:120'],
            'category' => ['required', Rule::in(['Core', 'Elective', 'Archived'])],
            'color' => ['nullable', 'string', 'max:30'],
        ];
    }
}
