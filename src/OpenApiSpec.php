<?php

namespace App;

use OpenApi\Attributes as OAT;

#[OAT\OpenApi(
    openapi: '3.0.0',
    info: new OAT\Info(
        version: '1.0.0',
        title: 'Online Shop API',
        description: 'üK295 LB1 — authentication, users, products, and categories.'
    ),
    servers: [new OAT\Server(url: '/', description: 'Application root')],
    tags: [
        new OAT\Tag(name: 'General', description: 'Health and welcome'),
        new OAT\Tag(name: 'Auth', description: 'Authentication'),
        new OAT\Tag(name: 'Product', description: 'Product catalog'),
        new OAT\Tag(name: 'Category', description: 'Product categories'),
        new OAT\Tag(name: 'User', description: 'API user accounts'),
    ]
)]
#[OAT\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    description: 'JWT from POST /api/v1/authenticate',
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
    required: ['username', 'password'],
    properties: [
        new OAT\Property(property: 'username', type: 'string', example: 'admin'),
        new OAT\Property(property: 'password', type: 'string', format: 'password', example: 'sec!ReT423*&'),
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
    schema: 'Product',
    required: ['id', 'sku', 'name', 'price', 'stock', 'active'],
    properties: [
        new OAT\Property(property: 'id', type: 'integer', example: 12345678),
        new OAT\Property(property: 'sku', type: 'string', example: '12345678'),
        new OAT\Property(property: 'name', type: 'string', example: 'CsBe-Logo'),
        new OAT\Property(property: 'id_category', type: 'integer', nullable: true, example: 1),
        new OAT\Property(property: 'price', type: 'number', format: 'float', example: 39999.95),
        new OAT\Property(property: 'description', type: 'string', nullable: true),
        new OAT\Property(property: 'image', type: 'string', nullable: true),
        new OAT\Property(property: 'stock', type: 'integer', example: 3),
        new OAT\Property(property: 'active', type: 'integer', example: 1),
    ],
    type: 'object'
)]
#[OAT\Schema(
    schema: 'ProductInput',
    required: ['name', 'price', 'active'],
    properties: [
        new OAT\Property(property: 'sku', type: 'string'),
        new OAT\Property(property: 'name', type: 'string'),
        new OAT\Property(property: 'price', type: 'number', format: 'float'),
        new OAT\Property(property: 'id_category', type: 'integer', nullable: true),
        new OAT\Property(property: 'description', type: 'string', nullable: true),
        new OAT\Property(property: 'image', type: 'string', nullable: true),
        new OAT\Property(property: 'stock', type: 'integer'),
        new OAT\Property(property: 'active', type: 'integer', example: 1),
    ],
    type: 'object'
)]
#[OAT\Schema(
    schema: 'Category',
    required: ['id', 'name', 'active'],
    properties: [
        new OAT\Property(property: 'id', type: 'integer', example: 1),
        new OAT\Property(property: 'name', type: 'string', example: 'Firmen-Logos'),
        new OAT\Property(property: 'active', type: 'integer', example: 1),
    ],
    type: 'object'
)]
#[OAT\Schema(
    schema: 'CategoryInput',
    required: ['name', 'active'],
    properties: [
        new OAT\Property(property: 'name', type: 'string', example: 'Firmen-Logos'),
        new OAT\Property(property: 'active', type: 'integer', example: 1),
    ],
    type: 'object'
)]
#[OAT\Schema(
    schema: 'User',
    required: ['username'],
    properties: [
        new OAT\Property(property: 'username', type: 'string', example: 'admin'),
    ],
    type: 'object'
)]
#[OAT\Schema(
    schema: 'UserInput',
    required: ['username', 'password'],
    properties: [
        new OAT\Property(property: 'username', type: 'string', example: 'shopper1'),
        new OAT\Property(property: 'password', type: 'string', format: 'password'),
    ],
    type: 'object'
)]
#[OAT\Schema(
    schema: 'UserPatch',
    required: ['password'],
    properties: [
        new OAT\Property(property: 'password', type: 'string', format: 'password'),
    ],
    type: 'object'
)]
#[OAT\Schema(
    schema: 'CategoryPatch',
    properties: [
        new OAT\Property(property: 'name', type: 'string'),
        new OAT\Property(property: 'active', type: 'integer', example: 0),
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
            schema: new OAT\Schema(type: 'string', example: 'Online Shop API — use /api/v1')
        )),
    ]
)]
class OpenApiSpec
{
}
