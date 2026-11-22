<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;
use App\Http\Resources\ScheduleResource;
use App\Services\ScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /** Initializes the ScheduleController dependencies. */
    public function __construct(
        private readonly ScheduleService $schedules,
    ) {}

    /** Returns the list response for the authenticated user. */
    public function index(Request $request): JsonResponse
    {
        $schedules = $this->schedules->list($request->user()->id);

        return response()->json([
            'data' => [
                'schedules' => ScheduleResource::collection($schedules)->resolve(),
            ],
        ]);
    }

    /** Creates a new record for the authenticated user. */
    public function store(StoreScheduleRequest $request): JsonResponse
    {
        $schedule = $this->schedules->create($request->user()->id, $request->validated());

        return response()->json([
            'data' => ['schedule' => new ScheduleResource($schedule)],
        ], 201);
    }

    /** Updates a record owned by the authenticated user. */
    public function update(UpdateScheduleRequest $request, int $schedule): JsonResponse
    {
        $updated = $this->schedules->update($schedule, $request->user()->id, $request->validated());

        if (! $updated) {
            return response()->json(['message' => 'Schedule not found'], 404);
        }

        return response()->json([
            'data' => ['schedule' => new ScheduleResource($updated)],
        ]);
    }

    /** Deletes a record owned by the authenticated user. */
    public function destroy(Request $request, int $schedule): JsonResponse
    {
        if (! $this->schedules->delete($schedule, $request->user()->id)) {
            return response()->json(['message' => 'Schedule not found'], 404);
        }

        return response()->json(['message' => 'Schedule deleted']);
    }
}
