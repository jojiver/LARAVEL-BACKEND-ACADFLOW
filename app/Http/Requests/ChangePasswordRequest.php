<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
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
            'currentPassword' => ['required', 'string'],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
            ],
            'passwordConfirmation' => ['required', 'string', 'same:password'],
        ];
    }
}
