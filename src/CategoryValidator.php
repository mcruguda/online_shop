<?php

namespace App;

use InvalidArgumentException;

final class CategoryValidator
{
    private const CREATE_KEYS = ["name", "active"];
    private const PATCH_KEYS = ["name", "active"];

    /**
     * @param array<string, mixed> $body
     */
    public static function fromCreateRequest(array $body): Category
    {
        Validation::assertOnlyKeys($body, self::CREATE_KEYS);
        Validation::requireKeys($body, self::CREATE_KEYS);

        return new Category(
            Validation::plainTextNonEmpty($body["name"], "name", 500),
            Validation::activeFlag($body["active"])
        );
    }

    /**
     * @param array<string, mixed> $body
     * @return array{name?: string, active?: int}
     */
    public static function patchFields(array $body): array
    {
        Validation::assertOnlyKeys($body, self::PATCH_KEYS);

        if ($body === []) {
            throw new InvalidArgumentException("No fields to update");
        }

        $fields = [];

        if (array_key_exists("name", $body)) {
            $fields["name"] = Validation::plainTextNonEmpty($body["name"], "name", 500);
        }

        if (array_key_exists("active", $body)) {
            $fields["active"] = Validation::activeFlag($body["active"]);
        }

        return $fields;
    }
}
