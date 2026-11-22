<?php

namespace App\Repositories\Contracts;

use App\Models\ClassSchedule;
use Illuminate\Database\Eloquent\Collection;

interface ScheduleRepositoryInterface
{
    /** Returns all ScheduleRepositoryInterface records owned by a user. */
    public function allForUser(int $userId): Collection;

    /** Finds one ScheduleRepositoryInterface record owned by a user. */
    public function findForUser(int $scheduleId, int $userId): ?ClassSchedule;

    /** Creates a new record for a user. */
    public function create(int $userId, array $data): ClassSchedule;

    /** Updates a record owned by the authenticated user. */
    public function update(ClassSchedule $schedule, array $data): ClassSchedule;

    /** Deletes a record owned by a user. */
    public function delete(ClassSchedule $schedule): void;
}
