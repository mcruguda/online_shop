<?php

namespace App;

use InvalidArgumentException;

class Product
{
    private int $productId;
    private string $sku;
    private string $name;
    private float $price;
    private ?int $idCategory;
    private ?string $description;
    private ?string $image;
    private int $stock;
    private int $active;

    public function __construct(
        int $productId,
        string $sku,
        string $name,
        float $price,
        ?int $idCategory,
        int $active = 1,
        ?string $description = null,
        ?string $image = null,
        int $stock = 0
    ) {
        $sku = trim($sku);
        $name = trim($name);
        $description = $description !== null ? trim($description) : null;
        $image = $image !== null ? trim($image) : null;

        if ($productId < 1) {
            throw new InvalidArgumentException("product id is required");
        }

        if ($sku === "") {
            throw new InvalidArgumentException("sku is required");
        }

        if ($name === "") {
            throw new InvalidArgumentException("name is required");
        }

        if ($price < 0) {
            throw new InvalidArgumentException("price must be zero or greater");
        }

        if ($stock < 0) {
            throw new InvalidArgumentException("stock must be zero or greater");
        }

        if ($active !== 0 && $active !== 1) {
            throw new InvalidArgumentException("active must be 0 or 1");
        }

        if ($description === "") {
            $description = null;
        }

        if ($image === "") {
            $image = null;
        }

        $this->productId = $productId;
        $this->sku = $sku;
        $this->name = $name;
        $this->price = $price;
        $this->idCategory = $idCategory;
        $this->description = $description;
        $this->image = $image;
        $this->stock = $stock;
        $this->active = $active;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getIdCategory(): ?int
    {
        return $this->idCategory;
    }

    public function toArray(): array
    {
        return [
            "id" => $this->productId,
            "sku" => $this->sku,
            "name" => $this->name,
            "price" => $this->price,
            "id_category" => $this->idCategory,
            "description" => $this->description,
            "image" => $this->image,
            "stock" => $this->stock,
            "active" => $this->active,
        ];
    }
}
