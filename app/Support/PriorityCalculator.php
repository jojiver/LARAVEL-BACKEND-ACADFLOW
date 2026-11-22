<?php

namespace App\Support;

use App\Models\Task;
use Carbon\CarbonInterface;

class PriorityCalculator
{
    private const DIFFICULTY = ['Easy' => 5, 'Medium' => 10, 'Hard' => 15];

    private const IMPORTANCE = ['Low' => 7, 'Medium' => 14, 'High' => 20];

    /**
     * @return array{score:int,level:string,daysRemaining:int,factors:array<int,array{label:string,value:int,max:int,note:string}>}
     */
    public static function calculate(Task $task, ?CarbonInterface $now = null): array
    {
        $now = $now ?? now();
        $daysRemaining = (int) ceil($now->diffInDays($task->deadline, false));

        $deadline = $daysRemaining <= 0
            ? 30
            : self::clamp((int) round(30 - $daysRemaining * 2.4), 3, 30);
        $workload = self::clamp((int) round(($task->estimated_hours / 10) * 20), 0, 20);
        $difficulty = self::DIFFICULTY[$task->difficulty] ?? 10;
        $importance = self::IMPORTANCE[$task->importance] ?? 14;
        $remaining = self::clamp(
            (int) round(((100 - $task->progress) / 100) * 15),
            0,
            15,
        );

        $score = $deadline + $workload + $difficulty + $importance + $remaining;
        $level = $score >= 70 ? 'High' : ($score >= 45 ? 'Medium' : 'Low');

        return [
            'score' => $score,
            'level' => $level,
            'daysRemaining' => $daysRemaining,
            'factors' => [
                ['label' => 'Deadline', 'value' => $deadline, 'max' => 30, 'note' => self::deadlineNote($daysRemaining)],
                ['label' => 'Workload', 'value' => $workload, 'max' => 20, 'note' => $task->estimated_hours.'h estimated'],
                ['label' => 'Difficulty', 'value' => $difficulty, 'max' => 15, 'note' => $task->difficulty],
                ['label' => 'Importance', 'value' => $importance, 'max' => 20, 'note' => $task->importance],
                ['label' => 'Remaining', 'value' => $remaining, 'max' => 15, 'note' => (100 - $task->progress).'% left'],
            ],
        ];
    }

    /** Performs clamp in PriorityCalculator. */
    private static function clamp(int $value, int $min, int $max): int
    {
        return min($max, max($min, $value));
    }

    /** Performs deadline note in PriorityCalculator. */
    private static function deadlineNote(int $days): string
    {
        if ($days < 0) return abs($days).'d overdue';
        if ($days === 0) return 'Due today';
        if ($days === 1) return 'Due tomorrow';
        return 'Due in '.$days.'d';
    }
}
