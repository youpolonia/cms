<?php
/**
 * Password Utilities
 * Provides secure password hashing and verification
 */
class PasswordUtils {
    const DEFAULT_ALGO = PASSWORD_BCRYPT;
    const DEFAULT_OPTIONS = ['cost' => 12];

    /**
     * Hash a password
     */
    public static function hash(string $password): string {
        return password_hash($password, self::DEFAULT_ALGO, self::DEFAULT_OPTIONS);
    }

    /**
     * Verify a password against a hash
     */
    public static function verify(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    /**
     * Check if a hash needs rehashing
     */
    public static function needsRehash(string $hash): bool {
        return password_needs_rehash($hash, self::DEFAULT_ALGO, self::DEFAULT_OPTIONS);
    }
}
