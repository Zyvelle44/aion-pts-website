<?php

namespace App\Services;

/** Password rules shared by registration and change-password flows. */
class AionPasswordPolicy
{
    /**
     * These printable ASCII punctuation characters survive the legacy
     * scrCharFilter() used by the AION account database routines.
     */
    public const SPECIAL_PATTERN = '/[!#$&()*+.\/:<>?@\[\]^_{|}~]/';

    public static function errors(string $password, string $username): array
    {
        $errors = [];

        if (strlen($password) < 8 || strlen($password) > 16) {
            $errors[] = 'Password must be 8–16 characters long.';
        }
        if (! preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter (A–Z).';
        }
        if (! preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter (a–z).';
        }
        if (! preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one digit (0–9).';
        }
        if (! preg_match(self::SPECIAL_PATTERN, $password)) {
            $errors[] = 'Password must contain at least one supported special character.';
        }
        if (! preg_match('/\A[\x21-\x7E]+\z/', $password)) {
            $errors[] = 'Password must use English letters and printable ASCII characters only, with no spaces.';
        }
        if ($username !== '' && stripos($password, $username) !== false) {
            $errors[] = 'Password must not contain your login username.';
        }
        if (AionRegistrationService::scrCharFilter($password) !== $password) {
            $errors[] = 'Password contains a character that is not supported by the legacy AION account system.';
        }

        return $errors;
    }

    public static function isValid(string $password, string $username): bool
    {
        return self::errors($password, $username) === [];
    }
}
