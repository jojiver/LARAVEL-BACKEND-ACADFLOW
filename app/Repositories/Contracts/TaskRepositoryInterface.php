<?php

namespace App\Repositories\Contracts;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

interface TaskRepositoryInterface
{
    /** Returns all TaskRepositoryInterface records owned by a user. */
    public function allForUser(int $userId): Collection;

    /** Finds one TaskRepositoryInterface record owned by a user. */
    public function findForUser(int $taskId, int $userId): ?Task;

    /** Creates a new record for a user. */
    public function create(int $userId, array $data, ?array $steps): Task;

    /** Updates a record owned by the authenticated user. */
    public function update(Task $task, array $data, ?array $steps): Task;

    /** Deletes a record owned by a user. */
    public function delete(Task $task): void;
}
