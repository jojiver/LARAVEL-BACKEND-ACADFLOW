<?php

namespace App\Repositories\Contracts;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

interface TaskRepositoryInterface
{
    public function allForUser(int $userId): Collection;

    public function findForUser(int $taskId, int $userId): ?Task;

    public function create(int $userId, array $data, ?array $steps): Task;

    public function update(Task $task, array $data, ?array $steps): Task;

    public function delete(Task $task): void;
}
