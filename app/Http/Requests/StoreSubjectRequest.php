<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubjectRequest extends FormRequest
{
    /** Authorizes the request before validation runs. */
    public function authorize(): bool
    {
        return true;
    }

    /** Returns the validation rules for this request. */
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
