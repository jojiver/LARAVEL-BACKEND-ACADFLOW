<?php

namespace App\Services;

use App\Models\ClassSchedule;
use App\Repositories\Contracts\ScheduleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ScheduleService
{
    /** Initializes the ScheduleService dependencies. */
    public function __construct(
        private readonly ScheduleRepositoryInterface $schedules,
    ) {}

    /** Returns the list of records for a user. */
    public function list(int $userId): Collection
    {
        return $this->schedules->allForUser($userId);
    }

    /** Creates a new record for a user. */
    public function create(int $userId, array $data): ClassSchedule
    {
        return $this->schedules->create($userId, [
            'subject_id' => $data['subjectId'],
            'day' => $data['day'],
            'start_time' => $data['startTime'],
            'end_time' => $data['endTime'],
            'room' => $data['room'] ?? null,
        ]);
    }

    /** Updates a record owned by the authenticated user. */
    public function update(int $scheduleId, int $userId, array $data): ?ClassSchedule
    {
        $schedule = $this->schedules->findForUser($scheduleId, $userId);

        if (! $schedule) {
            return null;
        }

        $attributes = [];
        foreach ([
            'subjectId' => 'subject_id',
            'day' => 'day',
            'startTime' => 'start_time',
            'endTime' => 'end_time',
            'room' => 'room',
        ] as $input => $column) {
            if (array_key_exists($input, $data)) {
                $attributes[$column] = $data[$input];
            }
        }

        return $this->schedules->update($schedule, $attributes);
    }

    /** Deletes a record owned by a user. */
    public function delete(int $scheduleId, int $userId): bool
    {
        $schedule = $this->schedules->findForUser($scheduleId, $userId);

        if (! $schedule) {
            return false;
        }

        $this->schedules->delete($schedule);

        return true;
    }
}
