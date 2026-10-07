<?php

namespace App;

use PDO;

class UserRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function verifyCredentials(string $email, string $password): bool
    {
        $statement = $this->pdo->prepare(
            "SELECT password_hash FROM users WHERE email = :email"
        );
        $statement->execute(["email" => strtolower($email)]);
        $row = $statement->fetch();

        if ($row === false) {
            return false;
        }

        return password_verify($password, $row["password_hash"]);
    }

    public function create(string $email, string $password): bool
    {
        $email = strtolower(trim($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $statement = $this->pdo->prepare("SELECT 1 FROM users WHERE email = :email");
        $statement->execute(["email" => $email]);

        if ($statement->fetch() !== false) {
            return false;
        }

        $insert = $this->pdo->prepare(
            "INSERT INTO users (email, password_hash) VALUES (:email, :password_hash)"
        );
        $insert->execute([
            "email" => $email,
            "password_hash" => password_hash($password, PASSWORD_DEFAULT),
        ]);

        return true;
    }
}
