<?php

namespace App\Repositories\Eloquent;

use App\Models\Subject;
use App\Repositories\Contracts\SubjectRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentSubjectRepository implements SubjectRepositoryInterface
{
    /** Returns all EloquentSubjectRepository records owned by a user. */
    public function allForUser(int $userId): Collection
    {
        return Subject::query()
            ->where('user_id', $userId)
            ->orderBy('name')
            ->get();
    }

    /** Finds one EloquentSubjectRepository record owned by a user. */
    public function findForUser(int $subjectId, int $userId): ?Subject
    {
        return Subject::query()
            ->where('user_id', $userId)
            ->find($subjectId);
    }

    /** Creates a new record for a user. */
    public function create(int $userId, array $data): Subject
    {
        return Subject::query()->create([
            'user_id' => $userId,
            ...$data,
        ]);
    }

    /** Updates a record owned by the authenticated user. */
    public function update(Subject $subject, array $data): Subject
    {
        $subject->fill($data)->save();

        return $subject->refresh();
    }

    /** Deletes a record owned by a user. */
    public function delete(Subject $subject): void
    {
        $subject->delete();
    }
}
