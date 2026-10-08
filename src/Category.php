<?php

namespace App;

use InvalidArgumentException;

class Category
{
    private ?int $id;
    private string $name;
    private int $active;

    public function __construct(string $name, int $active = 1, ?int $id = null)
    {
        $name = trim($name);

        if ($name === "") {
            throw new InvalidArgumentException("name is required");
        }

        if ($active !== 0 && $active !== 1) {
            throw new InvalidArgumentException("active must be 0 or 1");
        }

        $this->id = $id;
        $this->name = $name;
        $this->active = $active;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getActive(): int
    {
        return $this->active;
    }

    public function toArray(): array
    {
        $data = [
            "name" => $this->name,
            "active" => $this->active,
        ];

        if ($this->id !== null) {
            $data["id"] = $this->id;
        }

        return $data;
    }
}
