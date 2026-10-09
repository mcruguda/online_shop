<?php

namespace App;

use InvalidArgumentException;
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
        try {
            $body = Validation::jsonBody($request);
            Validation::assertOnlyKeys($body, ["username", "password"]);
            Validation::requireKeys($body, ["username", "password"]);
            $username = Validation::username($body["username"]);
            $password = Validation::password($body["password"]);
        } catch (InvalidArgumentException $exception) {
            return $this->json($response, ["error" => $exception->getMessage()], 400);
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
            JsonResponse::encode($data)
        );

        return $response
            ->withHeader("Content-Type", "application/json")
            ->withStatus($status);
    }
}
