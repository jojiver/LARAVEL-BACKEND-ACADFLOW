<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePreferencesRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /** Initializes the SettingsController dependencies. */
    public function __construct(
        private readonly AuthService $auth,
    ) {}

    /** Returns saved notification preferences. */
    public function preferences(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [
                'preferences' => $this->auth->preferences($request->user()),
            ],
        ]);
    }

    /** Updates saved notification preferences. */
    public function updatePreferences(UpdatePreferencesRequest $request): JsonResponse
    {
        $preferences = $this->auth->updatePreferences(
            $request->user(),
            $request->validated(),
        );

        return response()->json([
            'data' => ['preferences' => $preferences],
        ]);
    }
}
