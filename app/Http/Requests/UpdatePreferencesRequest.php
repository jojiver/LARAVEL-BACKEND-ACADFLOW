<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePreferencesRequest extends FormRequest
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
            'deadline' => ['sometimes', 'boolean'],
            'class' => ['sometimes', 'boolean'],
            'priority' => ['sometimes', 'boolean'],
            'workload' => ['sometimes', 'boolean'],
            'timing' => ['sometimes', 'string', Rule::in(['30m', '1h', '1d', '3d'])],
        ];
    }
}
