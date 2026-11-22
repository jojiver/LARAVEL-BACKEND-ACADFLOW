<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => [
                'required',
                'email',
                'max:190',
                Rule::unique('users', 'email')->ignore($this->user()?->id),
            ],
            'program' => ['required', 'string', Rule::in(['BSIT', 'BSCS', 'BSIS', 'Other'])],
            'yearLevel' => ['required', 'string', Rule::in(['1st Year', '2nd Year', '3rd Year', '4th Year'])],
            'academicTerm' => ['required', 'string', Rule::in(['1st Semester', '2nd Semester', 'Summer'])],
        ];
    }
}
