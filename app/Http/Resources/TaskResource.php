<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /** Serializes the model into the API response shape. */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'title' => $this->title,
            'subjectId' => $this->subject_id ? (string) $this->subject_id : null,
            'subjectName' => $this->subject?->name,
            'type' => $this->type,
            'description' => $this->description,
            'deadline' => $this->deadline?->toIso8601String(),
            'estimatedHours' => $this->estimated_hours,
            'difficulty' => $this->difficulty,
            'importance' => $this->importance,
            'progress' => $this->progress,
            'status' => $this->status,
            'completedAt' => $this->completed_at?->toIso8601String(),
            'steps' => $this->steps->map(fn ($step) => [
                'id' => (string) $step->id,
                'label' => $step->label,
                'done' => $step->done,
            ])->values(),
            'priority' => $this->priority,
        ];
    }
}
