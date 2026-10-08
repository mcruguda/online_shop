<?php

/**
 * Full reset: drops tables and re-imports from online_shop.sql.
 * For first-time / non-destructive setup, use setup.php instead.
 *
 * CLI: php database/import.php
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

$sql = file_get_contents(__DIR__ . "/online_shop.sql");
$sql = preg_replace("/^--.*$/m", "", $sql);

if (!$mysqli->multi_query($sql)) {
    fwrite(STDERR, "Import failed: " . $mysqli->error . PHP_EOL);
    exit(1);
}

do {
    if ($result = $mysqli->store_result()) {
        $result->free();
    }
} while ($mysqli->more_results() && $mysqli->next_result());

$mysqli->select_db($db["name"]);
$result = $mysqli->query("SHOW TABLES");
$tables = [];
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

echo "Reset complete for `" . $db["name"] . "`. Tables: " . implode(", ", $tables) . PHP_EOL;

$mysqli->close();
