<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /** Serializes the model into the API response shape. */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'studentId' => $this->student_id,
            'program' => $this->program,
            'yearLevel' => $this->year_level,
            'academicTerm' => $this->academic_term,
        ];
    }
}
