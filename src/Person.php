<?php

namespace App;

use InvalidArgumentException;

class Person
{
    private string $name;
    private string $birthday;
    private string $email;

    public function __construct(string $name, string $birthday, string $email)
    {
        $name = trim($name);
        $birthday = trim($birthday);
        $email = strtolower(trim($email));

        if ($name === "" || $birthday === "") {
            throw new InvalidArgumentException("name and birthday are required");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email address");
        }

        $date = \DateTime::createFromFormat("Y-m-d", $birthday);
        if ($date === false || $date->format("Y-m-d") !== $birthday) {
            throw new InvalidArgumentException("birthday must be YYYY-MM-DD");
        }

        $this->name = $name;
        $this->birthday = $birthday;
        $this->email = $email;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBirthday(): string
    {
        return $this->birthday;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function toArray(): array
    {
        return [
            "name" => $this->name,
            "birthday" => $this->birthday,
            "email" => $this->email,
        ];
    }
}
