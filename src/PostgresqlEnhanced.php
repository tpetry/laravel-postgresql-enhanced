<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced;

class PostgresqlEnhanced
{
    private static ?string $dateFormat = null;

    private static bool $detectBinaryStrings = false;
    private static bool $useNativeBooleans = false;

    /**
     * Get the configured date format, or null to use Laravel's default.
     */
    public static function dateFormat(): ?string
    {
        return self::$dateFormat;
    }

    /**
     * Enable automatic bytea binding for string values containing NUL bytes or invalid UTF-8.
     * Opt-in because such a value bound for a non-bytea column is no longer sent as text: with real prepares PostgreSQL
     * raises an error, with emulated prepares it is silently stored as its hex text form (\x...). Without this option
     * these strings are cut off at the first NUL byte.
     * Binary data that is valid UTF-8 without NUL bytes can't be detected and is still sent as text, so PostgreSQL parses
     * it as bytea escape input (\x41 becomes A, a lone backslash raises an error).
     */
    public static function detectBinaryStrings(bool $enabled = true): void
    {
        self::$detectBinaryStrings = $enabled;
    }

    /**
     * Check if binary string detection is enabled.
     */
    public static function detectsBinaryStrings(): bool
    {
        return self::$detectBinaryStrings;
    }

    /**
     * Reset all configuration flags to their defaults.
     * Public API for users' own tests and long-running processes that re-register providers.
     */
    public static function reset(): void
    {
        self::$useNativeBooleans = false;
        self::$dateFormat = null;
        self::$detectBinaryStrings = false;
    }

    /**
     * Set the date format for DateTimeInterface bindings and Eloquent model defaults.
     * Pass null to keep Laravel's Y-m-d H:i:s. Recommended: Y-m-d\TH:i:s.up (microseconds + offset).
     */
    public static function useDateFormat(?string $format): void
    {
        self::$dateFormat = $format;
    }

    /**
     * Enable native PostgreSQL boolean binding instead of Laravel's 1/0 integers.
     * Opt-in because booleans are then sent as t/f (or true/false with emulated prepares) instead of 1/0: an integer or
     * smallint column rejects them, a text column stores t/f (true/false with emulated prepares), and a jsonb column fails with real prepares but accepts the
     * emulated true/false literals.
     */
    public static function useNativeBooleans(bool $enabled = true): void
    {
        self::$useNativeBooleans = $enabled;
    }

    /**
     * Check if native boolean binding is enabled.
     */
    public static function usesNativeBooleans(): bool
    {
        return self::$useNativeBooleans;
    }
}
