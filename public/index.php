<?php

use App\AuthController;
use App\AuthMiddleware;
use App\CategoryController;
use App\CategoryRepository;
use App\Database;
use App\ProductController;
use App\ProductRepository;
use App\TokenAuth;
use App\UserController;
use App\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . "/../vendor/autoload.php";

$config = require __DIR__ . "/../config.php";

$database = new Database($config["db"]);
$mysqli = $database->getConnection();

$tokenAuth = new TokenAuth(
    $config["jwt_secret"],
    $config["jwt_issuer"],
    $config["jwt_ttl"]
);

$usersRepo = new UserRepository($mysqli);
$categoriesRepo = new CategoryRepository($mysqli);
$productsRepo = new ProductRepository($mysqli, $categoriesRepo);

$products = new ProductController($productsRepo);
$categories = new CategoryController($categoriesRepo);
$users = new UserController($usersRepo);
$auth = new AuthController($usersRepo, $tokenAuth);

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

$basePath = detectBasePath();
if ($basePath !== "") {
    $app->setBasePath($basePath);
}

$app->get("/", function (Request $request, Response $response): Response {
    $response->getBody()->write("Online Shop API — use /api/v1");
    return $response;
});

// Paths match Bruno collection: http://localhost/api/v1/...
$app->post("/api/v1/authenticate", [$auth, "login"]);

$authMiddleware = new AuthMiddleware($tokenAuth);

$app->group("/api/v1", function ($group) use ($products, $categories, $users) {
    $group->get("/products", [$products, "list"]);
    $group->get("/product/{id}", [$products, "get"]);
    $group->get("/categories", [$categories, "list"]);
    $group->get("/category/{id}", [$categories, "get"]);

    $group->put("/product/{id}", [$products, "put"]);
    $group->delete("/product/{id}", [$products, "delete"]);
    $group->post("/category", [$categories, "post"]);
    $group->patch("/category/{id}", [$categories, "patch"]);
    $group->delete("/category/{id}", [$categories, "delete"]);

    $group->get("/users", [$users, "list"]);
    $group->post("/user", [$users, "post"]);
    $group->get("/user/{username}", [$users, "get"]);
    $group->patch("/user/{username}", [$users, "patch"]);
    $group->delete("/user/{username}", [$users, "delete"]);
})->add($authMiddleware);

$app->run();

/**
 * Bruno uses http://localhost/api/v1/... (DocumentRoot = public/).
 * Under XAMPP subfolder: http://localhost/online_shop/api/v1/...
 */
function detectBasePath(): string
{
    $scriptName = str_replace("\\", "/", $_SERVER["SCRIPT_NAME"] ?? "");
    $directory = rtrim(dirname($scriptName), "/");
    $requestPath = strtok($_SERVER["REQUEST_URI"] ?? "", "?") ?: "";

    if ($directory === "" || $directory === "/") {
        return "";
    }

    $basePath = $directory;
    if (str_ends_with($basePath, "/public")) {
        $basePath = substr($basePath, 0, -strlen("/public"));
    }

    // Rewrites from /api/v1 keep REQUEST_URI as /api/v1/... — do not apply /online_shop base path.
    if ($basePath !== "" && str_starts_with($requestPath, $basePath . "/")) {
        return $basePath;
    }

    return "";
}
