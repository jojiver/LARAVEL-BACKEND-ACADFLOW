<?php

namespace App\Repositories\Eloquent;

use App\Models\Subject;
use App\Repositories\Contracts\SubjectRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentSubjectRepository implements SubjectRepositoryInterface
{
    public function allForUser(int $userId): Collection
    {
        return Subject::query()
            ->where('user_id', $userId)
            ->orderBy('name')
            ->get();
    }

    public function findForUser(int $subjectId, int $userId): ?Subject
    {
        return Subject::query()
            ->where('user_id', $userId)
            ->find($subjectId);
    }

    public function create(int $userId, array $data): Subject
    {
        return Subject::query()->create([
            'user_id' => $userId,
            ...$data,
        ]);
    }

    public function update(Subject $subject, array $data): Subject
    {
        $subject->fill($data)->save();

        return $subject->refresh();
    }

    public function delete(Subject $subject): void
    {
        $subject->delete();
    }
}
