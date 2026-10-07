<?php

namespace App;

use PDO;

class Database
{
    private PDO $pdo;

    public function __construct(string $sqlitePath)
    {
        $directory = dirname($sqlitePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $this->pdo = new PDO("sqlite:" . $sqlitePath);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->createSchema();
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    private function createSchema(): void
    {
        $this->pdo->exec(
            "CREATE TABLE IF NOT EXISTS persons (
                email TEXT PRIMARY KEY,
                name TEXT NOT NULL,
                birthday TEXT NOT NULL
            )"
        );

        $this->pdo->exec(
            "CREATE TABLE IF NOT EXISTS users (
                email TEXT PRIMARY KEY,
                password_hash TEXT NOT NULL
            )"
        );

        $this->seedDefaultUser();
    }

    private function seedDefaultUser(): void
    {
        $statement = $this->pdo->prepare("SELECT 1 FROM users WHERE email = :email");
        $statement->execute(["email" => "admin@example.com"]);

        if ($statement->fetch() !== false) {
            return;
        }

        $insert = $this->pdo->prepare(
            "INSERT INTO users (email, password_hash) VALUES (:email, :password_hash)"
        );
        $insert->execute([
            "email" => "admin@example.com",
            "password_hash" => password_hash("secret123", PASSWORD_DEFAULT),
        ]);
    }
}
