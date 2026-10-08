<?php

namespace App;

use mysqli;
use mysqli_sql_exception;

class Database
{
    private mysqli $mysqli;

    /**
     * @param array{host: string, port: string, name: string, user: string, password: string, charset: string} $config
     */
    public function __construct(array $config)
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->mysqli = new mysqli(
                $config["host"],
                $config["user"],
                $config["password"],
                $config["name"],
                (int) $config["port"]
            );
        } catch (mysqli_sql_exception $exception) {
            throw new \RuntimeException("MySQL connection failed: " . $exception->getMessage(), 0, $exception);
        }

        if (!$this->mysqli->set_charset($config["charset"])) {
            throw new \RuntimeException("Failed to set charset: " . $this->mysqli->error);
        }

        $this->ensureApiUser();
    }

    public function getConnection(): mysqli
    {
        return $this->mysqli;
    }

    private function ensureApiUser(): void
    {
        if (!$this->tableExists("users")) {
            $result = $this->mysqli->query("SELECT DATABASE() AS db");
            $row = $result->fetch_assoc();
            $dbName = $row["db"] ?? "";

            throw new \RuntimeException(
                "Table `users` is missing in database `" . $dbName
                . "`. Import database/online_shop.sql in phpMyAdmin (select database lb1_uek295 first)."
            );
        }

        $username = "admin";
        $statement = $this->mysqli->prepare("SELECT 1 FROM users WHERE username = ?");
        $statement->bind_param("s", $username);
        $statement->execute();
        $result = $statement->get_result();

        if ($result->fetch_assoc() !== null) {
            return;
        }

        $passwordHash = password_hash("sec!ReT423*&", PASSWORD_DEFAULT);
        $insert = $this->mysqli->prepare(
            "INSERT INTO users (username, password_hash) VALUES (?, ?)"
        );
        $insert->bind_param("ss", $username, $passwordHash);
        $insert->execute();
    }

    private function tableExists(string $table): bool
    {
        $statement = $this->mysqli->prepare(
            "SELECT 1 FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = ?"
        );
        $statement->bind_param("s", $table);
        $statement->execute();
        $result = $statement->get_result();

        return $result->fetch_assoc() !== null;
    }
}
