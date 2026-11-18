<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'studentId' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'program' => ['required', 'string', 'max:120'],
            'yearLevel' => ['required', 'string', 'max:50'],
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
