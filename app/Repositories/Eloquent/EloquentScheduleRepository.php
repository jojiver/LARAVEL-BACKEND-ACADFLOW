<?php

namespace App\Repositories\Eloquent;

use App\Models\ClassSchedule;
use App\Repositories\Contracts\ScheduleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentScheduleRepository implements ScheduleRepositoryInterface
{
    /** Returns all EloquentScheduleRepository records owned by a user. */
    public function allForUser(int $userId): Collection
    {
        return ClassSchedule::query()
            ->with('subject')
            ->where('user_id', $userId)
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();
    }

    /** Finds one EloquentScheduleRepository record owned by a user. */
    public function findForUser(int $scheduleId, int $userId): ?ClassSchedule
    {
        return ClassSchedule::query()
            ->with('subject')
            ->where('user_id', $userId)
            ->find($scheduleId);
    }

    /** Creates a new record for a user. */
    public function create(int $userId, array $data): ClassSchedule
    {
        $schedule = ClassSchedule::query()->create([
            'user_id' => $userId,
            ...$data,
        ]);

        return $schedule->load('subject');
    }

    /** Updates a record owned by the authenticated user. */
    public function update(ClassSchedule $schedule, array $data): ClassSchedule
    {
        $schedule->fill($data)->save();

        return $schedule->refresh()->load('subject');
    }

    /** Deletes a record owned by a user. */
    public function delete(ClassSchedule $schedule): void
    {
        $schedule->delete();
    }
}
