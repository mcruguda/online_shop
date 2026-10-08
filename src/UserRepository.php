<?php

namespace App;

use mysqli;

class UserRepository
{
    private mysqli $mysqli;

    public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function findAll(): array
    {
        $result = $this->mysqli->query(
            "SELECT username FROM users ORDER BY username"
        );

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = ["username" => $row["username"]];
        }

        return $users;
    }

    public function findByUsername(string $username): ?User
    {
        $statement = $this->mysqli->prepare(
            "SELECT username FROM users WHERE username = ?"
        );
        $statement->bind_param("s", $username);
        $statement->execute();
        $result = $statement->get_result();
        $row = $result->fetch_assoc();

        if ($row === null) {
            return null;
        }

        return new User($row["username"]);
    }

    public function verifyCredentials(string $username, string $password): bool
    {
        $statement = $this->mysqli->prepare(
            "SELECT password_hash FROM users WHERE username = ?"
        );
        $statement->bind_param("s", $username);
        $statement->execute();
        $result = $statement->get_result();
        $row = $result->fetch_assoc();

        if ($row === null) {
            return false;
        }

        return password_verify($password, $row["password_hash"]);
    }

    public function create(string $username, string $password): bool
    {
        if ($this->findByUsername($username) !== null) {
            return false;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $statement = $this->mysqli->prepare(
            "INSERT INTO users (username, password_hash) VALUES (?, ?)"
        );
        $statement->bind_param("ss", $username, $passwordHash);
        $statement->execute();

        return true;
    }

    public function updatePassword(string $username, string $password): bool
    {
        if ($this->findByUsername($username) === null) {
            return false;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $statement = $this->mysqli->prepare(
            "UPDATE users SET password_hash = ? WHERE username = ?"
        );
        $statement->bind_param("ss", $passwordHash, $username);
        $statement->execute();

        return $statement->affected_rows > 0;
    }

    public function delete(string $username): bool
    {
        $statement = $this->mysqli->prepare("DELETE FROM users WHERE username = ?");
        $statement->bind_param("s", $username);
        $statement->execute();

        return $statement->affected_rows > 0;
    }

    public function countAll(): int
    {
        $result = $this->mysqli->query("SELECT COUNT(*) AS total FROM users");
        $row = $result->fetch_assoc();

        return (int) ($row["total"] ?? 0);
    }
}
