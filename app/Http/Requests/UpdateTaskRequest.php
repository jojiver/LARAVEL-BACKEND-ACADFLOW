<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
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
            'title' => ['sometimes', 'required', 'string', 'max:160'],
            'subjectId' => ['sometimes', 'nullable', 'integer', 'exists:subjects,id'],
            'type' => ['sometimes', 'required', 'string', 'max:50'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'deadline' => ['sometimes', 'required', 'date'],
            'estimatedHours' => ['sometimes', 'required', 'numeric', 'min:0.5', 'max:100'],
            'difficulty' => ['sometimes', 'required', Rule::in(['Easy', 'Medium', 'Hard'])],
            'importance' => ['sometimes', 'required', Rule::in(['Low', 'Medium', 'High'])],
            'progress' => ['sometimes', 'required', 'integer', 'min:0', 'max:100'],
            'status' => [
                'sometimes',
                'required',
                Rule::in(['Not started', 'In progress', 'Almost done', 'Completed']),
            ],
            'steps' => ['sometimes', 'nullable', 'array'],
            'steps.*.id' => ['nullable', 'string'],
            'steps.*.label' => ['required', 'string', 'max:160'],
            'steps.*.done' => ['required', 'boolean'],
        ];
    }
}
