<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:160'],
            'subjectId' => ['nullable', 'integer', 'exists:subjects,id'],
            'type' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:2000'],
            'deadline' => ['required', 'date'],
            'estimatedHours' => ['required', 'numeric', 'min:0.5', 'max:100'],
            'difficulty' => ['required', Rule::in(['Easy', 'Medium', 'Hard'])],
            'importance' => ['required', Rule::in(['Low', 'Medium', 'High'])],
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
            'status' => [
                'required',
                Rule::in(['Not started', 'In progress', 'Almost done', 'Completed']),
            ],
            'steps' => ['nullable', 'array'],
            'steps.*.id' => ['nullable', 'string'],
            'steps.*.label' => ['required', 'string', 'max:160'],
            'steps.*.done' => ['required', 'boolean'],
        ];
    }
}
