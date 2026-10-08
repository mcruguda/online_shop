<?php

/**
 * Idempotent database setup — safe to run multiple times.
 *
 * CLI:  php database/setup.php
 * Uses database name and credentials from config.php (default: lb1_uek295).
 *
 * Creates the database, tables, and seed rows only when they are missing.
 * For a full reset (drop tables + re-import), run: php database/import.php
 */

$config = require __DIR__ . "/../config.php";
$db = $config["db"];

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$mysqli = new mysqli(
    $db["host"],
    $db["user"],
    $db["password"],
    "",
    (int) $db["port"]
);

$dbName = $db["name"];
$escapedDb = "`" . str_replace("`", "``", $dbName) . "`";

$mysqli->query(
    "CREATE DATABASE IF NOT EXISTS {$escapedDb}
     CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
);
$mysqli->select_db($dbName);
$mysqli->set_charset($db["charset"]);

createTablesIfNotExist($mysqli);

$seeded = seedIfEmpty($mysqli);

$result = $mysqli->query("SHOW TABLES");
$tables = [];
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

$message = "Database `{$dbName}` is ready. Tables: " . implode(", ", $tables);
if ($seeded !== []) {
    $message .= PHP_EOL . "Seeded: " . implode("; ", $seeded);
} else {
    $message .= PHP_EOL . "Seed data already present (nothing inserted).";
}

if (PHP_SAPI === "cli") {
    echo $message . PHP_EOL;
} else {
    header("Content-Type: text/plain; charset=utf-8");
    echo $message;
}

$mysqli->close();

function createTablesIfNotExist(mysqli $mysqli): void
{
    $mysqli->query(
        "CREATE TABLE IF NOT EXISTS `category` (
            `category_id` INT NOT NULL AUTO_INCREMENT,
            `active` TINYINT(1) NOT NULL DEFAULT 1,
            `name` VARCHAR(500) NOT NULL,
            PRIMARY KEY (`category_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $mysqli->query(
        "CREATE TABLE IF NOT EXISTS `product` (
            `product_id` INT NOT NULL AUTO_INCREMENT,
            `sku` VARCHAR(100) NOT NULL,
            `active` TINYINT(1) NOT NULL DEFAULT 1,
            `id_category` INT NULL,
            `name` VARCHAR(500) NOT NULL,
            `image` VARCHAR(1000) NULL,
            `description` TEXT NULL,
            `price` DECIMAL(65,2) NOT NULL,
            `stock` INT NOT NULL DEFAULT 0,
            PRIMARY KEY (`product_id`),
            UNIQUE KEY `uk_product_sku` (`sku`),
            KEY `idx_product_category` (`id_category`),
            CONSTRAINT `fk_product_category`
                FOREIGN KEY (`id_category`)
                REFERENCES `category` (`category_id`)
                ON DELETE SET NULL
                ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $mysqli->query(
        "CREATE TABLE IF NOT EXISTS `users` (
            `username` VARCHAR(100) NOT NULL,
            `password_hash` VARCHAR(255) NOT NULL,
            PRIMARY KEY (`username`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
}

/**
 * @return list<string> descriptions of what was seeded
 */
function seedIfEmpty(mysqli $mysqli): array
{
    $seeded = [];

    $result = $mysqli->query("SELECT COUNT(*) AS total FROM category");
    $row = $result->fetch_assoc();
    if ((int) ($row["total"] ?? 0) === 0) {
        $mysqli->query(
            "INSERT INTO category (category_id, active, name) VALUES (1, 1, 'Firmen-Logos')"
        );
        $seeded[] = "category Firmen-Logos (id 1)";
    }

    $username = "admin";
    $statement = $mysqli->prepare("SELECT 1 FROM users WHERE username = ?");
    $statement->bind_param("s", $username);
    $statement->execute();
    $check = $statement->get_result();
    if ($check->fetch_assoc() === null) {
        $passwordHash = password_hash("sec!ReT423*&", PASSWORD_DEFAULT);
        $insert = $mysqli->prepare(
            "INSERT INTO users (username, password_hash) VALUES (?, ?)"
        );
        $insert->bind_param("ss", $username, $passwordHash);
        $insert->execute();
        $seeded[] = "user admin";
    }

    return $seeded;
}
