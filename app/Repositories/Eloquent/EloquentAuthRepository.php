<?php

namespace App\Repositories\Eloquent;

use App\Models\ApiToken;
use App\Models\User;
use App\Repositories\Contracts\AuthRepositoryInterface;

class EloquentAuthRepository implements AuthRepositoryInterface
{
    /** Finds a user by email address. */
    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    /** Creates a new user record. */
    public function createUser(array $data): User
    {
        return User::query()->create($data);
    }

    /** Stores a hashed API token for a user. */
    public function createToken(User $user, string $tokenHash): ApiToken
    {
        return $user->apiTokens()->create([
            'token_hash' => $tokenHash,
        ]);
    }

    /** Finds a token by its hashed value. */
    public function findToken(string $tokenHash): ?ApiToken
    {
        return ApiToken::query()
            ->with('user')
            ->where('token_hash', $tokenHash)
            ->first();
    }

    /** Deletes one API token. */
    public function deleteToken(ApiToken $token): void
    {
        $token->delete();
    }

    /** Updates user attributes and refreshes the model. */
    public function updateUser(User $user, array $data): User
    {
        $user->fill($data)->save();

        return $user->refresh();
    }

    /** Deletes other sessions except the current token. */
    public function deleteOtherTokens(User $user, ?int $exceptTokenId = null): void
    {
        $query = $user->apiTokens();

        if ($exceptTokenId !== null) {
            $query->whereKeyNot($exceptTokenId);
        }

        $query->delete();
    }
}
