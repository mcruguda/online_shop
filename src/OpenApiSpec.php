<?php

namespace App;

use OpenApi\Attributes as OAT;

#[OAT\OpenApi(
    openapi: '3.0.0',
    info: new OAT\Info(
        version: '1.0.0',
        title: 'Person API',
        description: 'Slim REST API for user authentication and person records.'
    ),
    servers: [new OAT\Server(url: '/', description: 'Application root')],
    tags: [
        new OAT\Tag(name: 'General', description: 'Health and welcome'),
        new OAT\Tag(name: 'Auth', description: 'Registration and login'),
        new OAT\Tag(name: 'Person', description: 'Person CRUD'),
    ]
)]
#[OAT\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    description: 'JWT from POST /auth/login',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
#[OAT\Schema(
    schema: 'Error',
    required: ['error'],
    properties: [
        new OAT\Property(property: 'error', type: 'string', example: 'Invalid credentials'),
    ],
    type: 'object'
)]
#[OAT\Schema(
    schema: 'Credentials',
    required: ['email', 'password'],
    properties: [
        new OAT\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
        new OAT\Property(property: 'password', type: 'string', format: 'password', example: 'secret'),
    ],
    type: 'object'
)]
#[OAT\Schema(
    schema: 'LoginResponse',
    required: ['token', 'token_type', 'expires_in'],
    properties: [
        new OAT\Property(property: 'token', type: 'string'),
        new OAT\Property(property: 'token_type', type: 'string', example: 'Bearer'),
        new OAT\Property(property: 'expires_in', type: 'integer', example: 3600),
    ],
    type: 'object'
)]
#[OAT\Schema(
    schema: 'RegisterResponse',
    required: ['email'],
    properties: [
        new OAT\Property(property: 'email', type: 'string', format: 'email'),
    ],
    type: 'object'
)]
#[OAT\Schema(
    schema: 'Person',
    required: ['name', 'birthday', 'email'],
    properties: [
        new OAT\Property(property: 'name', type: 'string', example: 'Jane Doe'),
        new OAT\Property(property: 'birthday', type: 'string', format: 'date', example: '1990-05-15'),
        new OAT\Property(property: 'email', type: 'string', format: 'email', example: 'jane@example.com'),
    ],
    type: 'object'
)]
#[OAT\Get(
    path: '/',
    operationId: 'hello',
    summary: 'Welcome message',
    tags: ['General'],
    responses: [
        new OAT\Response(response: 200, description: 'Plain text greeting', content: new OAT\MediaType(
            mediaType: 'text/plain',
            schema: new OAT\Schema(type: 'string', example: 'Hello World')
        )),
    ]
)]
class OpenApiSpec
{
}
