<?php

namespace App;

use InvalidArgumentException;
use OpenApi\Attributes as OAT;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PersonController
{
    private PersonRepository $persons;

    public function __construct(PersonRepository $persons)
    {
        $this->persons = $persons;
    }

    #[OAT\Put(
        path: '/Person',
        operationId: 'personUpsert',
        summary: 'Create or update a person',
        security: [['bearerAuth' => []]],
        tags: ['Person'],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/Person')
        ),
        responses: [
            new OAT\Response(
                response: 201,
                description: 'Person created',
                content: new OAT\JsonContent(ref: '#/components/schemas/Person')
            ),
            new OAT\Response(
                response: 200,
                description: 'Person updated',
                content: new OAT\JsonContent(ref: '#/components/schemas/Person')
            ),
            new OAT\Response(
                response: 400,
                description: 'Validation error',
                content: new OAT\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OAT\Response(response: 401, description: 'Missing or invalid Bearer token'),
        ]
    )]
    public function put(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            $body = [];
        }

        $email = (string) ($body["email"] ?? "");

        try {
            $person = new Person(
                (string) ($body["name"] ?? ""),
                (string) ($body["birthday"] ?? ""),
                $email
            );
        } catch (InvalidArgumentException $exception) {
            return $this->json($response, ["error" => $exception->getMessage()], 400);
        }

        $created = $this->persons->save($person);

        return $this->json($response, $person->toArray(), $created ? 201 : 200);
    }

    #[OAT\Get(
        path: '/Person/{email}',
        operationId: 'personGet',
        summary: 'Get a person by email',
        tags: ['Person'],
        parameters: [
            new OAT\PathParameter(
                name: 'email',
                description: 'Person email address',
                required: true,
                schema: new OAT\Schema(type: 'string', format: 'email')
            ),
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Person found',
                content: new OAT\JsonContent(ref: '#/components/schemas/Person')
            ),
            new OAT\Response(
                response: 404,
                description: 'Person not found',
                content: new OAT\JsonContent(ref: '#/components/schemas/Error')
            ),
        ]
    )]
    public function get(Request $request, Response $response, array $args): Response
    {
        $person = $this->persons->findByEmail((string) $args["email"]);

        if ($person === null) {
            return $this->json($response, ["error" => "Person not found"], 404);
        }

        return $this->json($response, $person->toArray());
    }

    #[OAT\Delete(
        path: '/Person/{email}',
        operationId: 'personDelete',
        summary: 'Delete your own person record',
        security: [['bearerAuth' => []]],
        tags: ['Person'],
        parameters: [
            new OAT\PathParameter(
                name: 'email',
                description: 'Must match the authenticated user email',
                required: true,
                schema: new OAT\Schema(type: 'string', format: 'email')
            ),
        ],
        responses: [
            new OAT\Response(response: 204, description: 'Person deleted'),
            new OAT\Response(
                response: 403,
                description: 'Cannot delete another user\'s record',
                content: new OAT\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OAT\Response(
                response: 404,
                description: 'Person not found',
                content: new OAT\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OAT\Response(response: 401, description: 'Missing or invalid Bearer token'),
        ]
    )]
    public function delete(Request $request, Response $response, array $args): Response
    {
        $forbidden = $this->ensureOwnRecord($request, $response, (string) $args["email"]);
        if ($forbidden !== null) {
            return $forbidden;
        }

        $deleted = $this->persons->delete((string) $args["email"]);

        if (!$deleted) {
            return $this->json($response, ["error" => "Person not found"], 404);
        }

        return $response->withStatus(204);
    }

    private function ensureOwnRecord(Request $request, Response $response, string $email): ?Response
    {
        $authEmail = $request->getAttribute(AuthMiddleware::REQUEST_ATTRIBUTE);
        if (!is_string($authEmail)) {
            return $this->json($response, ["error" => "Unauthorized"], 401);
        }

        if (strtolower($email) !== $authEmail) {
            return $this->json($response, ["error" => "You can only modify your own person record"], 403);
        }

        return null;
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
