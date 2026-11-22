<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Http\Resources\SubjectResource;
use App\Services\SubjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    /** Initializes the SubjectController dependencies. */
    public function __construct(
        private readonly SubjectService $subjects,
    ) {}

    /** Returns the list response for the authenticated user. */
    public function index(Request $request): JsonResponse
    {
        $subjects = $this->subjects->list($request->user()->id);

        return response()->json([
            'data' => [
                'subjects' => SubjectResource::collection($subjects)->resolve(),
            ],
        ]);
    }

    /** Returns one record owned by the authenticated user. */
    public function show(Request $request, int $subject): JsonResponse
    {
        $found = $this->subjects->find($subject, $request->user()->id);

        if (! $found) {
            return response()->json(['message' => 'Subject not found'], 404);
        }

        return response()->json([
            'data' => ['subject' => new SubjectResource($found)],
        ]);
    }

    /** Creates a new record for the authenticated user. */
    public function store(StoreSubjectRequest $request): JsonResponse
    {
        $subject = $this->subjects->create($request->user()->id, $request->validated());

        return response()->json([
            'data' => ['subject' => new SubjectResource($subject)],
        ], 201);
    }

    /** Updates a record owned by the authenticated user. */
    public function update(UpdateSubjectRequest $request, int $subject): JsonResponse
    {
        $updated = $this->subjects->update($subject, $request->user()->id, $request->validated());

        if (! $updated) {
            return response()->json(['message' => 'Subject not found'], 404);
        }

        return response()->json([
            'data' => ['subject' => new SubjectResource($updated)],
        ]);
    }

    /** Deletes a record owned by the authenticated user. */
    public function destroy(Request $request, int $subject): JsonResponse
    {
        if (! $this->subjects->delete($subject, $request->user()->id)) {
            return response()->json(['message' => 'Subject not found'], 404);
        }

        return response()->json(['message' => 'Subject deleted']);
    }
}
