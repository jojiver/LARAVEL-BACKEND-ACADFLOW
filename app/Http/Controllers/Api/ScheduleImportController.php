<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmScheduleImportRequest;
use App\Http\Requests\ImportScheduleRequest;
use App\Services\ScheduleImportService;
use Illuminate\Http\JsonResponse;

class ScheduleImportController extends Controller
{
    /** Initializes the ScheduleImportController dependencies. */
    public function __construct(
        private readonly ScheduleImportService $imports,
    ) {}

    /** Builds a preview from an uploaded schedule file. */
    public function preview(ImportScheduleRequest $request): JsonResponse
    {
        $result = $this->imports->preview(
            $request->user()->id,
            $request->file('file'),
        );

        return response()->json(['data' => $result]);
    }

    /** Saves the selected imported schedule classes. */
    public function confirm(ConfirmScheduleImportRequest $request): JsonResponse
    {
        $result = $this->imports->confirm(
            $request->user()->id,
            $request->validated('classes'),
        );

        return response()->json(['data' => $result]);
    }
}
