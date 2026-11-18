<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:160'],
            'instructor' => ['sometimes', 'nullable', 'string', 'max:160'],
            'room' => ['sometimes', 'nullable', 'string', 'max:80'],
            'schedule' => ['sometimes', 'nullable', 'string', 'max:120'],
            'category' => ['sometimes', 'required', Rule::in(['Core', 'Elective', 'Archived'])],
            'color' => ['sometimes', 'nullable', 'string', 'max:30'],
        ];
    }
}
