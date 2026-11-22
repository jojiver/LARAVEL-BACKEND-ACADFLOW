<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /** Initializes the TaskController dependencies. */
    public function __construct(
        private readonly TaskService $tasks,
    ) {}

    /** Returns the list response for the authenticated user. */
    public function index(Request $request): JsonResponse
    {
        $tasks = $this->tasks->list($request->user()->id);

        return response()->json([
            'data' => ['tasks' => TaskResource::collection($tasks)->resolve()],
        ]);
    }

    /** Returns one record owned by the authenticated user. */
    public function show(Request $request, int $task): JsonResponse
    {
        $found = $this->tasks->find($task, $request->user()->id);

        if (! $found) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        return response()->json([
            'data' => ['task' => new TaskResource($found)],
        ]);
    }

    /** Creates a new record for the authenticated user. */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->tasks->create($request->user()->id, $request->validated());

        return response()->json([
            'data' => ['task' => new TaskResource($task)],
        ], 201);
    }

    /** Updates a record owned by the authenticated user. */
    public function update(UpdateTaskRequest $request, int $task): JsonResponse
    {
        $updated = $this->tasks->update($task, $request->user()->id, $request->validated());

        if (! $updated) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        return response()->json([
            'data' => ['task' => new TaskResource($updated)],
        ]);
    }

    /** Deletes a record owned by the authenticated user. */
    public function destroy(Request $request, int $task): JsonResponse
    {
        if (! $this->tasks->delete($task, $request->user()->id)) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        return response()->json(['message' => 'Task deleted']);
    }

    /** Calculates productivity statistics for a period. */
    public function productivity(Request $request): JsonResponse
    {
        $period = is_string($request->query('period'))
            ? $request->query('period')
            : 'week';

        $data = $this->tasks->productivity($request->user()->id, $period);
        $data['completedTasks'] = TaskResource::collection($data['completedTasks'])->resolve();

        return response()->json(['data' => $data]);
    }
}
