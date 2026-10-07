<?php

use App\AuthController;
use App\AuthMiddleware;
use App\Database;
use App\PersonController;
use App\PersonRepository;
use App\TokenAuth;
use App\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . "/../vendor/autoload.php";

$config = require __DIR__ . "/../config.php";

$database = new Database(__DIR__ . "/../data/persons.sqlite");
$pdo = $database->getConnection();

$tokenAuth = new TokenAuth(
    $config["jwt_secret"],
    $config["jwt_issuer"],
    $config["jwt_ttl"]
);

$persons = new PersonController(new PersonRepository($pdo));
$auth = new AuthController(new UserRepository($pdo), $tokenAuth);

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

$app->get("/", function (Request $request, Response $response): Response {
    $response->getBody()->write("Hello World");
    return $response;
});

$app->post("/auth/login", [$auth, "login"]);
$app->post("/auth/register", [$auth, "register"]);

$app->get("/Person/{email}", [$persons, "get"]);

$app->group("", function ($group) use ($persons) {
    $group->put("/Person", [$persons, "put"]);
    $group->delete("/Person/{email}", [$persons, "delete"]);
})->add(new AuthMiddleware($tokenAuth));

$app->run();
