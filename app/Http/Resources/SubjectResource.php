<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'instructor' => $this->instructor,
            'room' => $this->room,
            'schedule' => $this->schedule,
            'category' => $this->category,
            'color' => $this->color,
            'tasks' => $this->tasks_count ?? 0,
            'dueNow' => $this->due_now ?? 0,
            'progress' => $this->progress_summary ?? 0,
            'priority' => $this->priority_summary ?? 'Low',
        ];
    }
}
