<?php

namespace App\Repositories\Contracts;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;

interface SubjectRepositoryInterface
{
    /** Returns all SubjectRepositoryInterface records owned by a user. */
    public function allForUser(int $userId): Collection;

    /** Finds one SubjectRepositoryInterface record owned by a user. */
    public function findForUser(int $subjectId, int $userId): ?Subject;

    /** Creates a new record for a user. */
    public function create(int $userId, array $data): Subject;

    /** Updates a record owned by the authenticated user. */
    public function update(Subject $subject, array $data): Subject;

    /** Deletes a record owned by a user. */
    public function delete(Subject $subject): void;
}
