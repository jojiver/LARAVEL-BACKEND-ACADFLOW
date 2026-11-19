<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'studentId' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'program' => ['required', 'string', Rule::in(['BSIT', 'BSCS', 'BSIS', 'Other'])],
            'yearLevel' => ['required', 'string', Rule::in(['1st Year', '2nd Year', '3rd Year', '4th Year'])],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
            ],
        ];
    }
}
