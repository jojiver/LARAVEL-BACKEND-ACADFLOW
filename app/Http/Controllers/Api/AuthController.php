<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\ApiToken;
use App\Services\AuthService;
use App\Support\AuthCookie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /** Initializes the AuthController dependencies. */
    public function __construct(
        private readonly AuthService $auth,
    ) {}

    /** Returns a client CSRF token. */
    public function csrfToken(): JsonResponse
    {
        return response()->json([
            'data' => ['csrfToken' => Str::random(40)],
        ]);
    }

    /** Creates an account, signs the user in and sets the cookie. */
    public function signup(RegisterRequest $request): JsonResponse
    {
        [$user, $token] = $this->auth->register($request->validated());

        $response = response()->json([
            'data' => ['user' => new UserResource($user)],
        ], 201);

        AuthCookie::set($response, $token);

        return $response;
    }

    /** Authenticates credentials and issues a session cookie. */
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

    /** Checks whether the current session cookie is still valid. */
    public function refresh(Request $request): JsonResponse
    {
        $plainToken = $request->cookie(AuthCookie::NAME) ?: $request->bearerToken();

        if (! $plainToken || ! $this->auth->authenticate($plainToken)) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json(['data' => ['ok' => true]]);
    }

    /** Returns the currently authenticated user. */
    public function session(Request $request): JsonResponse
    {
        return response()->json([
            'data' => ['user' => new UserResource($request->user())],
        ]);
    }

    /** Revokes the current token and clears the auth cookie. */
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

    /** Updates the authenticated user profile. */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->auth->updateProfile($request->user(), $request->validated());

        return response()->json([
            'data' => ['user' => new UserResource($user)],
        ]);
    }

    /** Verifies the current password and changes it. */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        /** @var ApiToken|null $token */
        $token = $request->attributes->get('api_token');
        $data = $request->validated();

        $changed = $this->auth->changePassword(
            $request->user(),
            $data['currentPassword'],
            $data['password'],
            $token,
        );

        if (! $changed) {
            return response()->json([
                'message' => 'The current password is incorrect.',
            ], 422);
        }

        return response()->json(['data' => ['ok' => true]]);
    }
}
