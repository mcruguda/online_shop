<?php

namespace App;

use InvalidArgumentException;

/**
 * Defense-in-depth for user input. SQL safety still relies on prepared statements
 * in repositories; these checks reduce XSS and risky byte sequences before bind.
 */
final class SecurityInput
{
    public static function assertSafeForDb(string $value, string $field): string
    {
        if (str_contains($value, "\0")) {
            throw new InvalidArgumentException("{$field} contains invalid characters");
        }

        return $value;
    }

    public static function assertNoMarkup(string $value, string $field): string
    {
        self::assertSafeForDb($value, $field);

        if ($value !== strip_tags($value)) {
            throw new InvalidArgumentException("{$field} must not contain HTML");
        }

        return $value;
    }

    public static function assertHttpUrl(string $value, string $field): string
    {
        self::assertNoMarkup($value, $field);

        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException("{$field} must be a valid URL");
        }

        $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));
        if ($scheme !== "http" && $scheme !== "https") {
            throw new InvalidArgumentException("{$field} must use http or https");
        }

        return $value;
    }

    public static function assertSkuFormat(string $value, string $field = "sku"): string
    {
        self::assertSafeForDb($value, $field);

        if (!preg_match("/^[A-Za-z0-9._-]+$/", $value)) {
            throw new InvalidArgumentException(
                "{$field} may only contain letters, numbers, dots, underscores, and hyphens"
            );
        }

        return $value;
    }
}
