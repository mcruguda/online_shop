<?php

namespace App;

final class ProductValidator
{
    private const ALLOWED_KEYS = [
        "sku",
        "name",
        "price",
        "id_category",
        "description",
        "image",
        "stock",
        "active",
    ];

    /**
     * @param array<string, mixed> $body
     */
    public static function fromPutRequest(int $productId, array $body): Product
    {
        Validation::assertOnlyKeys($body, self::ALLOWED_KEYS);
        Validation::requireKeys($body, ["name", "price", "active"]);

        $sku = array_key_exists("sku", $body)
            ? Validation::sku($body["sku"])
            : (string) $productId;

        $name = Validation::plainTextNonEmpty($body["name"], "name", 500);
        $price = Validation::price($body["price"]);
        $active = Validation::activeFlag($body["active"]);
        $stock = Validation::optionalStock($body["stock"] ?? null, array_key_exists("stock", $body));

        $categoryId = array_key_exists("id_category", $body)
            ? Validation::categoryId($body["id_category"])
            : null;

        $description = array_key_exists("description", $body)
            ? Validation::optionalPlainText($body["description"], "description", 65535)
            : null;

        $image = array_key_exists("image", $body)
            ? Validation::optionalHttpUrl($body["image"], "image", 1000)
            : null;

        return new Product(
            $productId,
            $sku,
            $name,
            $price,
            $categoryId,
            $active,
            $description,
            $image,
            $stock
        );
    }
}
