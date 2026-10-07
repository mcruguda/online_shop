<?php

require __DIR__ . '/../vendor/autoload.php';

$result = (new \OpenApi\Builder())
    ->addSource(__DIR__ . '/../src')
    ->build();

$format = strtolower((string) ($_GET['format'] ?? ''));

if ($format === 'json') {
    header('Content-Type: application/json; charset=utf-8');
    echo $result->toJson();
    return;
}

header('Content-Type: application/x-yaml; charset=utf-8');
echo $result->toYaml();
