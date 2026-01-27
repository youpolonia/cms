<?php
/**
 * User form validation helpers
 */

function validateUsername(string $username): bool {
    return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
}

function validateEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword(string $password): bool {
    return strlen($password) >= 8;
}

function validateRole(string $role): bool {
    return in_array($role, ['editor', 'admin']);
}

function validatePasswordMatch(string $password, string $confirm): bool {
    return $password === $confirm;
}
