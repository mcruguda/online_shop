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
        path: '/auth/login',
        operationId: 'authLogin',
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
                description: 'Missing email or password',
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

        $email = trim((string) ($body["email"] ?? ""));
        $password = (string) ($body["password"] ?? "");

        if ($email === "" || $password === "") {
            return $this->json($response, ["error" => "Email and password are required"], 400);
        }

        if (!$this->users->verifyCredentials($email, $password)) {
            return $this->json($response, ["error" => "Invalid credentials"], 401);
        }

        return $this->json($response, [
            "token" => $this->tokenAuth->createForUser($email),
            "token_type" => "Bearer",
            "expires_in" => $this->tokenAuth->getTtl(),
        ]);
    }

    #[OAT\Post(
        path: '/auth/register',
        operationId: 'authRegister',
        summary: 'Create a user account',
        tags: ['Auth'],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/Credentials')
        ),
        responses: [
            new OAT\Response(
                response: 201,
                description: 'User created',
                content: new OAT\JsonContent(ref: '#/components/schemas/RegisterResponse')
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
        ]
    )]
    public function register(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            $body = [];
        }

        $email = trim((string) ($body["email"] ?? ""));
        $password = (string) ($body["password"] ?? "");

        if ($email === "" || $password === "") {
            return $this->json($response, ["error" => "Email and password are required"], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json($response, ["error" => "Invalid email address"], 400);
        }

        if (!$this->users->create($email, $password)) {
            return $this->json($response, ["error" => "User already exists"], 409);
        }

        return $this->json($response, ["email" => strtolower($email)], 201);
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
