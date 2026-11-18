<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\ApiToken;
use App\Services\AuthService;
use App\Support\AuthCookie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $auth,
    ) {}

    public function csrfToken(): JsonResponse
    {
        return response()->json([
            'data' => ['csrfToken' => Str::random(40)],
        ]);
    }

    public function signup(RegisterRequest $request): JsonResponse
    {
        [$user, $token] = $this->auth->register($request->validated());

        $response = response()->json([
            'data' => ['user' => new UserResource($user)],
        ], 201);

        AuthCookie::set($response, $token);

        return $response;
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->auth->login(
            $request->string('email')->toString(),
            $request->string('password')->toString(),
        );

        if (! $result) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], 422);
        }

        [$user, $token] = $result;

        $response = response()->json([
            'data' => ['user' => new UserResource($user)],
        ]);

        AuthCookie::set($response, $token);

        return $response;
    }

    public function refresh(Request $request): JsonResponse
    {
        $plainToken = $request->cookie(AuthCookie::NAME) ?: $request->bearerToken();

        if (! $plainToken || ! $this->auth->authenticate($plainToken)) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json(['data' => ['ok' => true]]);
    }

    public function session(Request $request): JsonResponse
    {
        return response()->json([
            'data' => ['user' => new UserResource($request->user())],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var ApiToken|null $token */
        $token = $request->attributes->get('api_token');

        if ($token) {
            $this->auth->logout($token);
        }

        $response = response()->json(['data' => ['ok' => true]]);
        AuthCookie::clear($response);

        return $response;
    }
}
