<?php

namespace App\Repositories\Eloquent;

use App\Models\ClassSchedule;
use App\Repositories\Contracts\ScheduleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentScheduleRepository implements ScheduleRepositoryInterface
{
    public function allForUser(int $userId): Collection
    {
        return ClassSchedule::query()
            ->with('subject')
            ->where('user_id', $userId)
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();
    }

    public function findForUser(int $scheduleId, int $userId): ?ClassSchedule
    {
        return ClassSchedule::query()
            ->with('subject')
            ->where('user_id', $userId)
            ->find($scheduleId);
    }

    public function create(int $userId, array $data): ClassSchedule
    {
        $schedule = ClassSchedule::query()->create([
            'user_id' => $userId,
            ...$data,
        ]);

        return $schedule->load('subject');
    }

    public function update(ClassSchedule $schedule, array $data): ClassSchedule
    {
        $schedule->fill($data)->save();

        return $schedule->refresh()->load('subject');
    }

    public function delete(ClassSchedule $schedule): void
    {
        $schedule->delete();
    }
}
