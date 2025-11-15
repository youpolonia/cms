<?php

namespace Includes\Auth;

interface UserProvider
{
    /**
     * Retrieve a user by their unique identifier.
     */
    public function retrieveById($id): ?array;

    /**
     * Retrieve a user by their credentials.
     */
    public function retrieveByCredentials(array $credentials): ?array;

    /**
     * Validate a user against the given credentials.
     */
    public function validateCredentials(array $user, array $credentials): bool;

    /**
     * Update the remember token for the given user.
     */
    public function updateRememberToken($userId, string $token, int $expires): bool;

    /**
     * Retrieve a user by their remember token.
     */
    public function retrieveByToken(string $token): ?array;

    /**
     * Send email verification notification.
     */
    public function sendVerificationEmail(array $user): bool;

    /**
     * Verify user's email using token.
     */
    public function verifyEmail(array $user, string $token): bool;

    /**
     * Check if user has verified email.
     */
    public function hasVerifiedEmail(array $user): bool;
}
