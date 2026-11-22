<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePreferencesRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct(
        private readonly AuthService $auth,
    ) {}

    public function preferences(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [
                'preferences' => $this->auth->preferences($request->user()),
            ],
        ]);
    }

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
