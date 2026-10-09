<?php

namespace App;

use InvalidArgumentException;
use OpenApi\Attributes as OAT;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CategoryController
{
    private CategoryRepository $categories;

    public function __construct(CategoryRepository $categories)
    {
        $this->categories = $categories;
    }

    #[OAT\Get(
        path: '/api/v1/categories',
        operationId: 'categoryList',
        summary: 'List categories',
        security: [['bearerAuth' => []]],
        tags: ['Category'],
        responses: [
            new OAT\Response(response: 401, description: 'Missing or invalid Bearer token'),
            new OAT\Response(
                response: 200,
                description: 'Category list',
                content: new OAT\JsonContent(
                    type: 'array',
                    items: new OAT\Items(ref: '#/components/schemas/Category')
                )
            ),
        ]
    )]
    public function list(Request $request, Response $response): Response
    {
        return $this->json($response, $this->categories->findAll());
    }

    #[OAT\Post(
        path: '/api/v1/category',
        operationId: 'categoryCreate',
        summary: 'Create a category',
        security: [['bearerAuth' => []]],
        tags: ['Category'],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/CategoryInput')
        ),
        responses: [
            new OAT\Response(
                response: 201,
                description: 'Category created',
                content: new OAT\JsonContent(ref: '#/components/schemas/Category')
            ),
            new OAT\Response(
                response: 400,
                description: 'Validation error',
                content: new OAT\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OAT\Response(response: 401, description: 'Missing or invalid Bearer token'),
        ]
    )]
    public function post(Request $request, Response $response): Response
    {
        try {
            $category = CategoryValidator::fromCreateRequest(Validation::jsonBody($request));
        } catch (InvalidArgumentException $exception) {
            return $this->json($response, ["error" => $exception->getMessage()], 400);
        }

        $created = $this->categories->create($category);

        return $this->json($response, $created->toArray(), 201);
    }

    #[OAT\Patch(
        path: '/api/v1/category/{id}',
        operationId: 'categoryUpdate',
        summary: 'Update a category',
        security: [['bearerAuth' => []]],
        tags: ['Category'],
        parameters: [
            new OAT\PathParameter(
                name: 'id',
                description: 'Category id',
                required: true,
                schema: new OAT\Schema(type: 'integer')
            ),
        ],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/CategoryPatch')
        ),
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Category updated',
                content: new OAT\JsonContent(ref: '#/components/schemas/Category')
            ),
            new OAT\Response(
                response: 400,
                description: 'Validation error',
                content: new OAT\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OAT\Response(
                response: 404,
                description: 'Category not found',
                content: new OAT\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OAT\Response(response: 401, description: 'Missing or invalid Bearer token'),
        ]
    )]
    public function patch(Request $request, Response $response, array $args): Response
    {
        try {
            $id = Validation::pathPositiveInt($args["id"] ?? null, "id");
            $fields = CategoryValidator::patchFields(Validation::jsonBody($request));
            $updated = $this->categories->update($id, $fields);
        } catch (InvalidArgumentException $exception) {
            return $this->json($response, ["error" => $exception->getMessage()], 400);
        }

        if ($updated === null) {
            return $this->json($response, ["error" => "Category not found"], 404);
        }

        return $this->json($response, $updated->toArray());
    }

    #[OAT\Get(
        path: '/api/v1/category/{id}',
        operationId: 'categoryGet',
        summary: 'Get a category by id',
        security: [['bearerAuth' => []]],
        tags: ['Category'],
        parameters: [
            new OAT\PathParameter(
                name: 'id',
                description: 'Category id',
                required: true,
                schema: new OAT\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Category found',
                content: new OAT\JsonContent(ref: '#/components/schemas/Category')
            ),
            new OAT\Response(
                response: 404,
                description: 'Category not found',
                content: new OAT\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OAT\Response(response: 401, description: 'Missing or invalid Bearer token'),
        ]
    )]
    public function get(Request $request, Response $response, array $args): Response
    {
        try {
            $id = Validation::pathPositiveInt($args["id"] ?? null, "id");
        } catch (InvalidArgumentException $exception) {
            return $this->json($response, ["error" => $exception->getMessage()], 400);
        }

        $category = $this->categories->findById($id);

        if ($category === null) {
            return $this->json($response, ["error" => "Category not found"], 404);
        }

        return $this->json($response, $category->toArray());
    }

    #[OAT\Delete(
        path: '/api/v1/category/{id}',
        operationId: 'categoryDelete',
        summary: 'Delete a category',
        security: [['bearerAuth' => []]],
        tags: ['Category'],
        parameters: [
            new OAT\PathParameter(
                name: 'id',
                description: 'Category id',
                required: true,
                schema: new OAT\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OAT\Response(response: 204, description: 'Category deleted'),
            new OAT\Response(
                response: 404,
                description: 'Category not found',
                content: new OAT\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OAT\Response(response: 401, description: 'Missing or invalid Bearer token'),
        ]
    )]
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $id = Validation::pathPositiveInt($args["id"] ?? null, "id");
        } catch (InvalidArgumentException $exception) {
            return $this->json($response, ["error" => $exception->getMessage()], 400);
        }

        if ($this->categories->findById($id) === null) {
            return $this->json($response, ["error" => "Category not found"], 404);
        }

        $this->categories->delete($id);

        return $response->withStatus(204);
    }

    private function json(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(
            JsonResponse::encode($data)
        );

        return $response
            ->withHeader("Content-Type", "application/json")
            ->withStatus($status);
    }
}
