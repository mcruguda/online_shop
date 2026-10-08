<?php

namespace App;

use mysqli;

class ProductRepository
{
    private mysqli $mysqli;

    private CategoryRepository $categories;

    public function __construct(mysqli $mysqli, CategoryRepository $categories)
    {
        $this->mysqli = $mysqli;
        $this->categories = $categories;
    }

    public function findAll(): array
    {
        $result = $this->mysqli->query(
            "SELECT product_id, sku, name, price, id_category, description, image, stock, active
             FROM product ORDER BY product_id"
        );

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $this->rowToArray($row);
        }

        return $rows;
    }

    public function findById(int $id): ?Product
    {
        $statement = $this->mysqli->prepare(
            "SELECT product_id, sku, name, price, id_category, description, image, stock, active
             FROM product WHERE product_id = ?"
        );
        $statement->bind_param("i", $id);
        $statement->execute();
        $result = $statement->get_result();
        $row = $result->fetch_assoc();

        if ($row === null) {
            return null;
        }

        return $this->rowToProduct($row);
    }

    /**
     * @return array{product: Product, created: bool}
     */
    public function save(Product $product): array
    {
        $id = $product->getProductId();
        $existing = $this->findById($id);
        $params = $this->productParams($product);

        if ($existing === null) {
            $statement = $this->mysqli->prepare(
                "INSERT INTO product (product_id, sku, name, price, id_category, description, image, stock, active)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $this->bindProductParams($statement, $params);
            $statement->execute();

            $saved = $this->findById($id);
            if ($saved === null) {
                throw new \RuntimeException("Failed to load product after insert");
            }

            return ["product" => $saved, "created" => true];
        }

        $statement = $this->mysqli->prepare(
            "UPDATE product
             SET sku = ?, name = ?, price = ?, id_category = ?, description = ?, image = ?, stock = ?, active = ?
             WHERE product_id = ?"
        );
        $this->bindProductUpdateParams($statement, $params);
        $statement->execute();

        $saved = $this->findById($id);
        if ($saved === null) {
            throw new \RuntimeException("Failed to load product after update");
        }

        return ["product" => $saved, "created" => false];
    }

    public function delete(int $id): bool
    {
        $statement = $this->mysqli->prepare("DELETE FROM product WHERE product_id = ?");
        $statement->bind_param("i", $id);
        $statement->execute();

        return $statement->affected_rows > 0;
    }

    public function categoryExists(?int $categoryId): bool
    {
        if ($categoryId === null) {
            return true;
        }

        return $this->categories->findById($categoryId) !== null;
    }

    private function productParams(Product $product): array
    {
        $data = $product->toArray();

        return [
            "product_id" => $data["id"],
            "sku" => $data["sku"],
            "name" => $data["name"],
            "price" => $data["price"],
            "id_category" => $data["id_category"],
            "description" => $data["description"],
            "image" => $data["image"],
            "stock" => $data["stock"],
            "active" => $data["active"],
        ];
    }

    private function bindProductParams(\mysqli_stmt $statement, array $params): void
    {
        $productId = $params["product_id"];
        $sku = $params["sku"];
        $name = $params["name"];
        $price = $params["price"];
        $idCategory = $params["id_category"];
        $description = $params["description"];
        $image = $params["image"];
        $stock = $params["stock"];
        $active = $params["active"];

        $statement->bind_param(
            "issdisiii",
            $productId,
            $sku,
            $name,
            $price,
            $idCategory,
            $description,
            $image,
            $stock,
            $active
        );
    }

    private function bindProductUpdateParams(\mysqli_stmt $statement, array $params): void
    {
        $sku = $params["sku"];
        $name = $params["name"];
        $price = $params["price"];
        $idCategory = $params["id_category"];
        $description = $params["description"];
        $image = $params["image"];
        $stock = $params["stock"];
        $active = $params["active"];
        $productId = $params["product_id"];

        $statement->bind_param(
            "ssdissiii",
            $sku,
            $name,
            $price,
            $idCategory,
            $description,
            $image,
            $stock,
            $active,
            $productId
        );
    }

    private function rowToProduct(array $row): Product
    {
        return new Product(
            (int) $row["product_id"],
            $row["sku"],
            $row["name"],
            (float) $row["price"],
            $row["id_category"] !== null ? (int) $row["id_category"] : null,
            (int) $row["active"],
            $row["description"],
            $row["image"],
            (int) $row["stock"]
        );
    }

    private function rowToArray(array $row): array
    {
        return [
            "id" => (int) $row["product_id"],
            "sku" => $row["sku"],
            "name" => $row["name"],
            "price" => (float) $row["price"],
            "id_category" => $row["id_category"] !== null ? (int) $row["id_category"] : null,
            "description" => $row["description"],
            "image" => $row["image"],
            "stock" => (int) $row["stock"],
            "active" => (int) $row["active"],
        ];
    }
}
