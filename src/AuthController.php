<?php

namespace App;

use OpenApi\Attributes as OAT;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    private UserRepository $users;

    private TokenAuth $tokenAuth;

    public function __construct(UserRepository $users, TokenAuth $tokenAuth)
    {
        $this->users = $users;
        $this->tokenAuth = $tokenAuth;
    }

    #[OAT\Post(
        path: '/api/v1/authenticate',
        operationId: 'authenticate',
        summary: 'Obtain a JWT',
        tags: ['Auth'],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/Credentials')
        ),
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Authentication successful',
                content: new OAT\JsonContent(ref: '#/components/schemas/LoginResponse')
            ),
            new OAT\Response(
                response: 400,
                description: 'Missing username or password',
                content: new OAT\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OAT\Response(
                response: 401,
                description: 'Invalid credentials',
                content: new OAT\JsonContent(ref: '#/components/schemas/Error')
            ),
        ]
    )]
    public function login(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            $body = [];
        }

        $username = trim((string) ($body["username"] ?? ""));
        $password = (string) ($body["password"] ?? "");

        if ($username === "" || $password === "") {
            return $this->json($response, ["error" => "Username and password are required"], 400);
        }

        if (!$this->users->verifyCredentials($username, $password)) {
            return $this->json($response, ["error" => "Invalid credentials"], 401);
        }

        return $this->json($response, [
            "token" => $this->tokenAuth->createForUser($username),
            "token_type" => "Bearer",
            "expires_in" => $this->tokenAuth->getTtl(),
        ]);
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
