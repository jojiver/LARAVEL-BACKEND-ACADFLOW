<?php

namespace App\Services;

use App\Models\Subject;
use App\Repositories\Contracts\SubjectRepositoryInterface;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Support\PriorityCalculator;
use Illuminate\Database\Eloquent\Collection;

class SubjectService
{
    public function __construct(
        private readonly SubjectRepositoryInterface $subjects,
        private readonly TaskRepositoryInterface $tasks,
    ) {}

    public function list(int $userId): Collection
    {
        $subjects = $this->subjects->allForUser($userId);
        $tasks = $this->tasks->allForUser($userId);
        $now = now();

        foreach ($subjects as $subject) {
            $subjectTasks = $tasks->where('subject_id', $subject->id);
            $total = $subjectTasks->count();
            $completed = $subjectTasks->filter(fn ($task) => $task->completed_at)->count();
            $progress = $total === 0
                ? 0
                : (int) round(
                    $subjectTasks->sum(fn ($task) => $task->completed_at ? 100 : $task->progress) / $total
                );
            $dueNow = $subjectTasks->filter(function ($task) use ($now) {
                if ($task->completed_at) {
                    return false;
                }
                $days = (int) ceil($now->diffInDays($task->deadline, false));
                return $days >= 0 && $days <= 7;
            })->count();

            $levels = $subjectTasks->map(
                fn ($task) => PriorityCalculator::calculate($task, $now)['level']
            );
            $priority = $levels->contains('High')
                ? 'High'
                : ($levels->contains('Medium') ? 'Medium' : 'Low');

            $subject->setAttribute('tasks_count', $total);
            $subject->setAttribute('due_now', $dueNow);
            $subject->setAttribute('progress_summary', $progress);
            $subject->setAttribute('priority_summary', $priority);
            $subject->setAttribute('completed_count', $completed);
        }

        return $subjects;
    }

    public function find(int $subjectId, int $userId): ?Subject
    {
        return $this->subjects->findForUser($subjectId, $userId);
    }

    public function create(int $userId, array $data): Subject
    {
        return $this->subjects->create($userId, $data);
    }

    public function update(int $subjectId, int $userId, array $data): ?Subject
    {
        $subject = $this->subjects->findForUser($subjectId, $userId);

        if (! $subject) {
            return null;
        }

        return $this->subjects->update($subject, $data);
    }

    public function delete(int $subjectId, int $userId): bool
    {
        $subject = $this->subjects->findForUser($subjectId, $userId);

        if (! $subject) {
            return false;
        }

        $this->subjects->delete($subject);

        return true;
    }
}
