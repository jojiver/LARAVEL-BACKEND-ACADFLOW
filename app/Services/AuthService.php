<?php

namespace App\Services;

use App\Models\ApiToken;
use App\Models\User;
use App\Repositories\Contracts\AuthRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    public function __construct(
        private readonly AuthRepositoryInterface $auth,
    ) {}

    public function register(array $data): array
    {
        $user = $this->auth->createUser([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'student_id' => $data['studentId'] ?? null,
            'program' => $data['program'] ?? null,
            'year_level' => $data['yearLevel'] ?? null,
            'role' => 'STUDENT',
        ]);

        return [$user, $this->issueToken($user)];
    }

    public function login(string $email, string $password): ?array
    {
        $user = $this->auth->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        return [$user, $this->issueToken($user)];
    }

    public function logout(ApiToken $token): void
    {
        $this->auth->deleteToken($token);
    }

    public function updateProfile(User $user, array $data): User
    {
        return $this->auth->updateUser($user, [
            'name' => $data['name'],
            'email' => $data['email'],
            'program' => $data['program'],
            'year_level' => $data['yearLevel'],
            'academic_term' => $data['academicTerm'] ?? null,
        ]);
    }

    public function changePassword(
        User $user,
        string $currentPassword,
        string $newPassword,
        ?ApiToken $currentToken = null,
    ): bool {
        if (! Hash::check($currentPassword, $user->password)) {
            return false;
        }

        $this->auth->updateUser($user, ['password' => $newPassword]);
        $this->auth->deleteOtherTokens($user, $currentToken?->id);

        return true;
    }

    public function preferences(User $user): array
    {
        return [
            'deadline' => (bool) $user->notify_deadline,
            'class' => (bool) $user->notify_class,
            'priority' => (bool) $user->notify_priority,
            'workload' => (bool) $user->notify_workload,
            'timing' => $user->reminder_timing ?? '1h',
        ];
    }

    public function updatePreferences(User $user, array $data): array
    {
        $attributes = [];

        foreach ([
            'deadline' => 'notify_deadline',
            'class' => 'notify_class',
            'priority' => 'notify_priority',
            'workload' => 'notify_workload',
            'timing' => 'reminder_timing',
        ] as $input => $column) {
            if (array_key_exists($input, $data)) {
                $attributes[$column] = $data[$input];
            }
        }

        $this->auth->updateUser($user, $attributes);

        return $this->preferences($user);
    }

    public function authenticate(string $plainToken): ?array
    {
        $token = $this->auth->findToken(hash('sha256', $plainToken));

        if (! $token || ! $token->user) {
            return null;
        }

        return [$token, $token->user];
    }

    private function issueToken(User $user): string
    {
        $plainToken = Str::random(60);

        $this->auth->createToken($user, hash('sha256', $plainToken));

        return $plainToken;
    }
}
