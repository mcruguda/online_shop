<?php

namespace App;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface as Request;

final class Validation
{
    /**
     * @return array<string, mixed>
     */
    public static function jsonBody(Request $request): array
    {
        $body = $request->getParsedBody();

        if ($body === null || $body === "") {
            throw new InvalidArgumentException("JSON body is required");
        }

        if (!is_array($body)) {
            throw new InvalidArgumentException("JSON body must be an object");
        }

        return $body;
    }

    /**
     * @param list<string> $allowed
     */
    public static function assertOnlyKeys(array $body, array $allowed): void
    {
        $extra = array_diff(array_keys($body), $allowed);
        if ($extra !== []) {
            sort($extra);
            throw new InvalidArgumentException(
                "unknown field(s): " . implode(", ", $extra)
            );
        }
    }

    /**
     * @param list<string> $keys
     */
    public static function requireKeys(array $body, array $keys): void
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $body)) {
                throw new InvalidArgumentException("{$key} is required");
            }
        }
    }

    public static function pathPositiveInt(mixed $value, string $label = "id"): int
    {
        if (!is_scalar($value)) {
            throw new InvalidArgumentException("{$label} must be a positive integer");
        }

        $text = trim((string) $value);
        if ($text === "" || !preg_match("/^[1-9][0-9]*$/", $text)) {
            throw new InvalidArgumentException("{$label} must be a positive integer");
        }

        return (int) $text;
    }

    public static function nonEmptyString(mixed $value, string $field, int $maxLength): string
    {
        if (!is_string($value) && !is_numeric($value)) {
            throw new InvalidArgumentException("{$field} must be a string");
        }

        $text = trim((string) $value);
        if ($text === "") {
            throw new InvalidArgumentException("{$field} is required");
        }

        if (strlen($text) > $maxLength) {
            throw new InvalidArgumentException(
                "{$field} must be at most {$maxLength} characters"
            );
        }

        return SecurityInput::assertSafeForDb($text, $field);
    }

    public static function plainTextNonEmpty(mixed $value, string $field, int $maxLength): string
    {
        return SecurityInput::assertNoMarkup(
            self::nonEmptyString($value, $field, $maxLength),
            $field
        );
    }

    public static function optionalPlainText(mixed $value, string $field, int $maxLength): ?string
    {
        $text = self::optionalNullableString($value, $field, $maxLength);
        if ($text === null) {
            return null;
        }

        return SecurityInput::assertNoMarkup($text, $field);
    }

    public static function optionalHttpUrl(mixed $value, string $field, int $maxLength): ?string
    {
        $text = self::optionalNullableString($value, $field, $maxLength);
        if ($text === null) {
            return null;
        }

        return SecurityInput::assertHttpUrl($text, $field);
    }

    public static function sku(mixed $value, string $field = "sku"): string
    {
        return SecurityInput::assertSkuFormat(self::nonEmptyString($value, $field, 100), $field);
    }

    public static function optionalNullableString(mixed $value, string $field, int $maxLength): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value) && !is_numeric($value)) {
            throw new InvalidArgumentException("{$field} must be a string or null");
        }

        $text = trim((string) $value);
        if ($text === "") {
            return null;
        }

        if (strlen($text) > $maxLength) {
            throw new InvalidArgumentException(
                "{$field} must be at most {$maxLength} characters"
            );
        }

        return SecurityInput::assertSafeForDb($text, $field);
    }

    public static function activeFlag(mixed $value, string $field = "active"): int
    {
        if ($value === 0 || $value === "0" || $value === false) {
            return 0;
        }

        if ($value === 1 || $value === "1" || $value === true) {
            return 1;
        }

        throw new InvalidArgumentException("{$field} must be 0 or 1");
    }

    public static function price(mixed $value): float
    {
        if (!is_int($value) && !is_float($value)) {
            if (!is_string($value) || !is_numeric($value)) {
                throw new InvalidArgumentException("price must be a number");
            }
        }

        $price = (float) $value;
        if ($price < 0) {
            throw new InvalidArgumentException("price must be zero or greater");
        }

        return $price;
    }

    public static function stock(mixed $value): int
    {
        if (!is_int($value) && !(is_string($value) && ctype_digit($value))) {
            if (!is_string($value) || !preg_match("/^[0-9]+$/", $value)) {
                throw new InvalidArgumentException("stock must be a non-negative integer");
            }
        }

        $stock = (int) $value;
        if ($stock < 0) {
            throw new InvalidArgumentException("stock must be zero or greater");
        }

        return $stock;
    }

    public static function optionalStock(mixed $value, bool $present): int
    {
        if (!$present) {
            return 0;
        }

        return self::stock($value);
    }

    /**
     * @return int|null category id, or null when explicitly null / omitted handling is caller-specific
     */
    public static function categoryId(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (!is_int($value) && !(is_string($value) && ctype_digit($value))) {
            throw new InvalidArgumentException("id_category must be a positive integer or null");
        }

        $id = (int) $value;
        if ($id < 1) {
            throw new InvalidArgumentException("id_category must be a positive integer or null");
        }

        return $id;
    }

    public static function username(mixed $value): string
    {
        return (new User(self::nonEmptyString($value, "username", 100)))->getUsername();
    }

    public static function password(mixed $value, string $field = "password"): string
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException("{$field} must be a string");
        }

        if ($value === "") {
            throw new InvalidArgumentException("{$field} is required");
        }

        if (strlen($value) > 255) {
            throw new InvalidArgumentException("{$field} must be at most 255 characters");
        }

        return SecurityInput::assertSafeForDb($value, $field);
    }

    public static function pathUsername(mixed $value): string
    {
        if (!is_scalar($value)) {
            throw new InvalidArgumentException("username is invalid");
        }

        return self::username((string) $value);
    }
}
