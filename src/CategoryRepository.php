<?php

namespace App;

use mysqli;

class CategoryRepository
{
    private mysqli $mysqli;

    public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function findAll(): array
    {
        $result = $this->mysqli->query(
            "SELECT category_id, name, active FROM category ORDER BY category_id"
        );

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $this->rowToArray($row);
        }

        return $rows;
    }

    public function findById(int $id): ?Category
    {
        $statement = $this->mysqli->prepare(
            "SELECT category_id, name, active FROM category WHERE category_id = ?"
        );
        $statement->bind_param("i", $id);
        $statement->execute();
        $result = $statement->get_result();
        $row = $result->fetch_assoc();

        if ($row === null) {
            return null;
        }

        return $this->rowToCategory($row);
    }

    public function create(Category $category): Category
    {
        $name = $category->getName();
        $active = $category->getActive();

        $statement = $this->mysqli->prepare(
            "INSERT INTO category (name, active) VALUES (?, ?)"
        );
        $statement->bind_param("si", $name, $active);
        $statement->execute();

        return new Category(
            $category->getName(),
            $category->getActive(),
            (int) $this->mysqli->insert_id
        );
    }

    public function update(int $id, array $fields): ?Category
    {
        $existing = $this->findById($id);
        if ($existing === null) {
            return null;
        }

        $name = array_key_exists("name", $fields)
            ? trim((string) $fields["name"])
            : $existing->getName();
        $active = array_key_exists("active", $fields)
            ? (int) $fields["active"]
            : $existing->getActive();

        if ($name === "") {
            throw new \InvalidArgumentException("name is required");
        }

        if ($active !== 0 && $active !== 1) {
            throw new \InvalidArgumentException("active must be 0 or 1");
        }

        $statement = $this->mysqli->prepare(
            "UPDATE category SET name = ?, active = ? WHERE category_id = ?"
        );
        $statement->bind_param("sii", $name, $active, $id);
        $statement->execute();

        return new Category($name, $active, $id);
    }

    public function delete(int $id): bool
    {
        $statement = $this->mysqli->prepare("DELETE FROM category WHERE category_id = ?");
        $statement->bind_param("i", $id);
        $statement->execute();

        return $statement->affected_rows > 0;
    }

    public function countProducts(int $id): int
    {
        $statement = $this->mysqli->prepare(
            "SELECT COUNT(*) AS total FROM product WHERE id_category = ?"
        );
        $statement->bind_param("i", $id);
        $statement->execute();
        $result = $statement->get_result();
        $row = $result->fetch_assoc();

        return (int) ($row["total"] ?? 0);
    }

    private function rowToCategory(array $row): Category
    {
        return new Category(
            $row["name"],
            (int) $row["active"],
            (int) $row["category_id"]
        );
    }

    private function rowToArray(array $row): array
    {
        return [
            "id" => (int) $row["category_id"],
            "name" => $row["name"],
            "active" => (int) $row["active"],
        ];
    }
}
