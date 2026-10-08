<?php

namespace App;

use InvalidArgumentException;
use OpenApi\Attributes as OAT;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProductController
{
    private ProductRepository $products;

    public function __construct(ProductRepository $products)
    {
        $this->products = $products;
    }

    #[OAT\Get(
        path: '/api/v1/products',
        operationId: 'productList',
        summary: 'List products',
        security: [['bearerAuth' => []]],
        tags: ['Product'],
        responses: [
            new OAT\Response(response: 401, description: 'Missing or invalid Bearer token'),
            new OAT\Response(
                response: 200,
                description: 'Product list',
                content: new OAT\JsonContent(
                    type: 'array',
                    items: new OAT\Items(ref: '#/components/schemas/Product')
                )
            ),
        ]
    )]
    public function list(Request $request, Response $response): Response
    {
        return $this->json($response, $this->products->findAll());
    }

    #[OAT\Put(
        path: '/api/v1/product/{id}',
        operationId: 'productUpsert',
        summary: 'Create or update a product',
        security: [['bearerAuth' => []]],
        tags: ['Product'],
        parameters: [
            new OAT\PathParameter(
                name: 'id',
                description: 'Product id',
                required: true,
                schema: new OAT\Schema(type: 'integer')
            ),
        ],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/ProductInput')
        ),
        responses: [
            new OAT\Response(
                response: 201,
                description: 'Product created',
                content: new OAT\JsonContent(ref: '#/components/schemas/Product')
            ),
            new OAT\Response(
                response: 200,
                description: 'Product updated',
                content: new OAT\JsonContent(ref: '#/components/schemas/Product')
            ),
            new OAT\Response(
                response: 400,
                description: 'Validation error',
                content: new OAT\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OAT\Response(
                response: 404,
                description: 'Unknown category',
                content: new OAT\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OAT\Response(response: 401, description: 'Missing or invalid Bearer token'),
        ]
    )]
    public function put(Request $request, Response $response, array $args): Response
    {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            $body = [];
        }

        $id = (int) $args["id"];
        $categoryId = array_key_exists("id_category", $body) && $body["id_category"] !== null
            ? (int) $body["id_category"]
            : null;
        if ($categoryId !== null && $categoryId < 1) {
            $categoryId = null;
        }
        if (!$this->products->categoryExists($categoryId)) {
            return $this->json($response, ["error" => "Category not found"], 404);
        }

        $sku = trim((string) ($body["sku"] ?? (string) $id));
        if ($sku === "") {
            $sku = (string) $id;
        }

        try {
            $product = new Product(
                $id,
                $sku,
                (string) ($body["name"] ?? ""),
                (float) ($body["price"] ?? -1),
                $categoryId,
                (int) ($body["active"] ?? 1),
                isset($body["description"]) ? (string) $body["description"] : null,
                isset($body["image"]) ? (string) $body["image"] : null,
                (int) ($body["stock"] ?? 0)
            );
        } catch (InvalidArgumentException $exception) {
            return $this->json($response, ["error" => $exception->getMessage()], 400);
        }

        $result = $this->products->save($product);

        return $this->json(
            $response,
            $result["product"]->toArray(),
            $result["created"] ? 201 : 200
        );
    }

    #[OAT\Get(
        path: '/api/v1/product/{id}',
        operationId: 'productGet',
        summary: 'Get a product by id',
        security: [['bearerAuth' => []]],
        tags: ['Product'],
        parameters: [
            new OAT\PathParameter(
                name: 'id',
                description: 'Product id',
                required: true,
                schema: new OAT\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Product found',
                content: new OAT\JsonContent(ref: '#/components/schemas/Product')
            ),
            new OAT\Response(
                response: 404,
                description: 'Product not found',
                content: new OAT\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OAT\Response(response: 401, description: 'Missing or invalid Bearer token'),
        ]
    )]
    public function get(Request $request, Response $response, array $args): Response
    {
        $product = $this->products->findById((int) $args["id"]);

        if ($product === null) {
            return $this->json($response, ["error" => "Product not found"], 404);
        }

        return $this->json($response, $product->toArray());
    }

    #[OAT\Delete(
        path: '/api/v1/product/{id}',
        operationId: 'productDelete',
        summary: 'Delete a product',
        security: [['bearerAuth' => []]],
        tags: ['Product'],
        parameters: [
            new OAT\PathParameter(
                name: 'id',
                description: 'Product id',
                required: true,
                schema: new OAT\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OAT\Response(response: 204, description: 'Product deleted'),
            new OAT\Response(
                response: 404,
                description: 'Product not found',
                content: new OAT\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OAT\Response(response: 401, description: 'Missing or invalid Bearer token'),
        ]
    )]
    public function delete(Request $request, Response $response, array $args): Response
    {
        $deleted = $this->products->delete((int) $args["id"]);

        if (!$deleted) {
            return $this->json($response, ["error" => "Product not found"], 404);
        }

        return $response->withStatus(204);
    }

    private function json(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );

        return $response
            ->withHeader("Content-Type", "application/json")
            ->withStatus($status);
    }
}
