<?php

namespace App\Repositories\Contracts;

use App\Models\ApiToken;
use App\Models\User;

interface AuthRepositoryInterface
{
    /** Finds a user by email address. */
    public function findByEmail(string $email): ?User;

    /** Creates a new user record. */
    public function createUser(array $data): User;

    /** Stores a hashed API token for a user. */
    public function createToken(User $user, string $tokenHash): ApiToken;

    /** Finds a token by its hashed value. */
    public function findToken(string $tokenHash): ?ApiToken;

    /** Deletes one API token. */
    public function deleteToken(ApiToken $token): void;

    /** Updates user attributes and refreshes the model. */
    public function updateUser(User $user, array $data): User;

    /** Deletes other sessions except the current token. */
    public function deleteOtherTokens(User $user, ?int $exceptTokenId = null): void;
}
