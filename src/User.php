<?php

namespace App;

use InvalidArgumentException;

class User
{
    private string $username;

    public function __construct(string $username)
    {
        $username = trim($username);

        if ($username === "") {
            throw new InvalidArgumentException("username is required");
        }

        if (strlen($username) > 100) {
            throw new InvalidArgumentException("username must be at most 100 characters");
        }

        if (!preg_match("/^[a-zA-Z0-9._-]+$/", $username)) {
            throw new InvalidArgumentException(
                "username may only contain letters, numbers, dots, underscores, and hyphens"
            );
        }

        $this->username = SecurityInput::assertSafeForDb($username, "username");
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function toArray(): array
    {
        return ["username" => $this->username];
    }
}
