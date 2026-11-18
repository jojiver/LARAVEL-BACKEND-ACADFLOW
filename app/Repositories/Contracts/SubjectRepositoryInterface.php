<?php

namespace App\Repositories\Contracts;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;

interface SubjectRepositoryInterface
{
    public function allForUser(int $userId): Collection;

    public function findForUser(int $subjectId, int $userId): ?Subject;

    public function create(int $userId, array $data): Subject;

    public function update(Subject $subject, array $data): Subject;

    public function delete(Subject $subject): void;
}
