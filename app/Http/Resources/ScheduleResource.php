<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    /** Serializes the model into the API response shape. */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'subjectId' => (string) $this->subject_id,
            'subjectName' => $this->subject?->name,
            'day' => $this->day,
            'startTime' => $this->start_time,
            'endTime' => $this->end_time,
            'room' => $this->room,
        ];
    }
}
