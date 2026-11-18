<?php

namespace App\Repositories\Contracts;

use App\Models\ClassSchedule;
use Illuminate\Database\Eloquent\Collection;

interface ScheduleRepositoryInterface
{
    public function allForUser(int $userId): Collection;

    public function findForUser(int $scheduleId, int $userId): ?ClassSchedule;

    public function create(int $userId, array $data): ClassSchedule;

    public function update(ClassSchedule $schedule, array $data): ClassSchedule;

    public function delete(ClassSchedule $schedule): void;
}
