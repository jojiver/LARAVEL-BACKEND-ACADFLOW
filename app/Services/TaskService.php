<?php

namespace App\Services;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Support\PriorityCalculator;
use Illuminate\Database\Eloquent\Collection;

class TaskService
{
    public function __construct(
        private readonly TaskRepositoryInterface $tasks,
    ) {}

    public function list(int $userId): Collection
    {
        return $this->tasks->allForUser($userId)->each(
            fn (Task $task) => $task->setAttribute('priority', PriorityCalculator::calculate($task)),
        );
    }

    public function find(int $taskId, int $userId): ?Task
    {
        $task = $this->tasks->findForUser($taskId, $userId);

        if ($task) {
            $task->setAttribute('priority', PriorityCalculator::calculate($task));
        }

        return $task;
    }

    public function create(int $userId, array $data): Task
    {
        $steps = $data['steps'] ?? null;
        unset($data['steps']);

        $attributes = $this->attributes($data);
        $attributes['progress'] = $this->progress($attributes['progress'], $steps);

        if ($attributes['progress'] >= 100) {
            $attributes['status'] = 'Completed';
        }
        if ($attributes['status'] === 'Completed') {
            $attributes['progress'] = 100;
        }

        $attributes['completed_at'] = $this->completedAt($attributes['status'], $attributes['progress']);

        $task = $this->tasks->create($userId, $attributes, $steps);
        $task->setAttribute('priority', PriorityCalculator::calculate($task));

        return $task;
    }

    public function update(int $taskId, int $userId, array $data): ?Task
    {
        $task = $this->tasks->findForUser($taskId, $userId);

        if (! $task) {
            return null;
        }

        $steps = array_key_exists('steps', $data) ? $data['steps'] : null;
        unset($data['steps']);

        $attributes = $this->attributes($data);
        $progress = $steps !== null
            ? $this->progress($task->progress, $steps)
            : ($attributes['progress'] ?? $task->progress);
        $status = $attributes['status'] ?? $task->status;

        if ($progress >= 100) {
            $status = 'Completed';
        }

        $attributes['status'] = $status;
        $attributes['progress'] = $status === 'Completed' ? 100 : $progress;
        $attributes['completed_at'] = $this->completedAt($status, $attributes['progress']);

        $updated = $this->tasks->update($task, $attributes, $steps);
        $updated->setAttribute('priority', PriorityCalculator::calculate($updated));

        return $updated;
    }

    public function delete(int $taskId, int $userId): bool
    {
        $task = $this->tasks->findForUser($taskId, $userId);

        if (! $task) {
            return false;
        }

        $this->tasks->delete($task);

        return true;
    }

    public function productivity(int $userId, string $period): array
    {
        $all = $this->tasks->allForUser($userId)->each(
            fn (Task $task) => $task->setAttribute('priority', PriorityCalculator::calculate($task)),
        );

        $span = match ($period) {
            'today' => 1,
            'month' => 30,
            default => 7,
        };

        $now = now();
        $start = $now->copy()->subDays($span - 1)->startOfDay();
        $end = $now->copy()->addDays($span);

        $completed = $all
            ->filter(fn (Task $task) => $task->completed_at
                && $task->completed_at->greaterThanOrEqualTo($start)
                && $task->completed_at->lessThanOrEqualTo($end))
            ->sortByDesc(fn (Task $task) => $task->completed_at)
            ->values();

        $open = $all->filter(
            fn (Task $task) => ! $task->completed_at
                && $task->progress < 100
                && $task->deadline->greaterThanOrEqualTo($now)
                && $task->deadline->lessThanOrEqualTo($end)
        );

        $hours = round($completed->sum('estimated_hours'), 1);
        $total = $completed->count() + $open->count();

        return [
            'period' => in_array($period, ['today', 'week', 'month'], true) ? $period : 'week',
            'completed' => $completed->count(),
            'hours' => $hours,
            'open' => $open->count(),
            'rate' => $total === 0 ? 0 : (int) round(($completed->count() / $total) * 100),
            'completedTasks' => $completed,
        ];
    }

    private function attributes(array $data): array
    {
        $map = [
            'title' => 'title',
            'subjectId' => 'subject_id',
            'type' => 'type',
            'description' => 'description',
            'deadline' => 'deadline',
            'estimatedHours' => 'estimated_hours',
            'difficulty' => 'difficulty',
            'importance' => 'importance',
            'progress' => 'progress',
            'status' => 'status',
        ];

        $attributes = [];
        foreach ($map as $input => $column) {
            if (array_key_exists($input, $data)) {
                $attributes[$column] = $data[$input];
            }
        }

        return $attributes;
    }

    private function progress(int $fallback, ?array $steps): int
    {
        if ($steps === null || count($steps) === 0) {
            return $fallback;
        }

        $done = count(array_filter($steps, fn ($step) => ! empty($step['done'])));

        return (int) round(($done / count($steps)) * 100);
    }

    private function completedAt(string $status, int $progress): mixed
    {
        return $status === 'Completed' || $progress >= 100 ? now() : null;
    }
}
