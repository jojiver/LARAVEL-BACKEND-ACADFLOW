<?php

namespace App\Repositories\Eloquent;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EloquentTaskRepository implements TaskRepositoryInterface
{
    /** Returns all EloquentTaskRepository records owned by a user. */
    public function allForUser(int $userId): Collection
    {
        return Task::query()
            ->with(['subject', 'steps'])
            ->where('user_id', $userId)
            ->orderBy('deadline')
            ->get();
    }

    /** Finds one EloquentTaskRepository record owned by a user. */
    public function findForUser(int $taskId, int $userId): ?Task
    {
        return Task::query()
            ->with(['subject', 'steps'])
            ->where('user_id', $userId)
            ->find($taskId);
    }

    /** Creates a new record for a user. */
    public function create(int $userId, array $data, ?array $steps): Task
    {
        return DB::transaction(function () use ($userId, $data, $steps) {
            $task = Task::query()->create([
                'user_id' => $userId,
                ...$data,
            ]);

            $this->replaceSteps($task, $steps);

            return $task->load(['subject', 'steps']);
        });
    }

    /** Updates a record owned by the authenticated user. */
    public function update(Task $task, array $data, ?array $steps): Task
    {
        return DB::transaction(function () use ($task, $data, $steps) {
            $task->fill($data)->save();

            if ($steps !== null) {
                $this->replaceSteps($task, $steps);
            }

            return $task->refresh()->load(['subject', 'steps']);
        });
    }

    /** Deletes a record owned by a user. */
    public function delete(Task $task): void
    {
        $task->delete();
    }

    /** Replaces a task's checklist steps. */
    private function replaceSteps(Task $task, ?array $steps): void
    {
        $task->steps()->delete();

        if (! $steps) {
            return;
        }

        foreach ($steps as $index => $step) {
            $task->steps()->create([
                'label' => $step['label'],
                'done' => (bool) ($step['done'] ?? false),
                'position' => $index,
            ]);
        }
    }
}
