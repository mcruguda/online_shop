<?php

namespace App;

use InvalidArgumentException;
use OpenApi\Attributes as OAT;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserController
{
    private UserRepository $users;

    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    #[OAT\Get(
        path: '/api/v1/users',
        operationId: 'userList',
        summary: 'List users',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'User list',
                content: new OAT\JsonContent(
                    type: 'array',
                    items: new OAT\Items(ref: '#/components/schemas/User')
                )
            ),
            new OAT\Response(response: 401, description: 'Missing or invalid Bearer token'),
        ]
    )]
    public function list(Request $request, Response $response): Response
    {
        return $this->json($response, $this->users->findAll());
    }

    #[OAT\Post(
        path: '/api/v1/user',
        operationId: 'userCreate',
        summary: 'Create a user',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/UserInput')
        ),
        responses: [
            new OAT\Response(
                response: 201,
                description: 'User created',
                content: new OAT\JsonContent(ref: '#/components/schemas/User')
            ),
            new OAT\Response(
                response: 400,
                description: 'Validation error',
                content: new OAT\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OAT\Response(
                response: 409,
                description: 'User already exists',
                content: new OAT\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OAT\Response(response: 401, description: 'Missing or invalid Bearer token'),
        ]
    )]
    public function post(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            $body = [];
        }

        $password = (string) ($body["password"] ?? "");
        if ($password === "") {
            return $this->json($response, ["error" => "password is required"], 400);
        }

        try {
            $user = new User((string) ($body["username"] ?? ""));
        } catch (InvalidArgumentException $exception) {
            return $this->json($response, ["error" => $exception->getMessage()], 400);
        }

        if (!$this->users->create($user->getUsername(), $password)) {
            return $this->json($response, ["error" => "User already exists"], 409);
        }

        return $this->json($response, $user->toArray(), 201);
    }

    #[OAT\Get(
        path: '/api/v1/user/{username}',
        operationId: 'userGet',
        summary: 'Get a user by username',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        parameters: [
            new OAT\PathParameter(
                name: 'username',
                required: true,
                schema: new OAT\Schema(type: 'string')
            ),
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'User found',
                content: new OAT\JsonContent(ref: '#/components/schemas/User')
            ),
            new OAT\Response(
                response: 404,
                description: 'User not found',
                content: new OAT\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OAT\Response(response: 401, description: 'Missing or invalid Bearer token'),
        ]
    )]
    public function get(Request $request, Response $response, array $args): Response
    {
        $user = $this->users->findByUsername((string) $args["username"]);

        if ($user === null) {
            return $this->json($response, ["error" => "User not found"], 404);
        }

        return $this->json($response, $user->toArray());
    }

    #[OAT\Patch(
        path: '/api/v1/user/{username}',
        operationId: 'userUpdate',
        summary: 'Update a user password',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        parameters: [
            new OAT\PathParameter(
                name: 'username',
                required: true,
                schema: new OAT\Schema(type: 'string')
            ),
        ],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/UserPatch')
        ),
        responses: [
            new OAT\Response(
                response: 200,
                description: 'User updated',
                content: new OAT\JsonContent(ref: '#/components/schemas/User')
            ),
            new OAT\Response(
                response: 400,
                description: 'Validation error',
                content: new OAT\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OAT\Response(
                response: 404,
                description: 'User not found',
                content: new OAT\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OAT\Response(response: 401, description: 'Missing or invalid Bearer token'),
        ]
    )]
    public function patch(Request $request, Response $response, array $args): Response
    {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            $body = [];
        }

        $username = (string) $args["username"];
        $password = (string) ($body["password"] ?? "");

        if ($password === "") {
            return $this->json($response, ["error" => "password is required"], 400);
        }

        if ($this->users->findByUsername($username) === null) {
            return $this->json($response, ["error" => "User not found"], 404);
        }

        $this->users->updatePassword($username, $password);

        return $this->json($response, ["username" => $username]);
    }

    #[OAT\Delete(
        path: '/api/v1/user/{username}',
        operationId: 'userDelete',
        summary: 'Delete a user',
        security: [['bearerAuth' => []]],
        tags: ['User'],
        parameters: [
            new OAT\PathParameter(
                name: 'username',
                required: true,
                schema: new OAT\Schema(type: 'string')
            ),
        ],
        responses: [
            new OAT\Response(response: 204, description: 'User deleted'),
            new OAT\Response(
                response: 404,
                description: 'User not found',
                content: new OAT\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OAT\Response(
                response: 409,
                description: 'Cannot delete the last user',
                content: new OAT\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OAT\Response(response: 401, description: 'Missing or invalid Bearer token'),
        ]
    )]
    public function delete(Request $request, Response $response, array $args): Response
    {
        $username = (string) $args["username"];

        if ($this->users->findByUsername($username) === null) {
            return $this->json($response, ["error" => "User not found"], 404);
        }

        if ($this->users->countAll() <= 1) {
            return $this->json($response, ["error" => "Cannot delete the last user"], 409);
        }

        $this->users->delete($username);

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
