<?php

namespace App;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpUnauthorizedException;

class AuthMiddleware implements MiddlewareInterface
{
    public const REQUEST_ATTRIBUTE = "authUserEmail";

    private TokenAuth $tokenAuth;

    public function __construct(TokenAuth $tokenAuth)
    {
        $this->tokenAuth = $tokenAuth;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $header = $request->getHeaderLine("Authorization");

        if (!preg_match("/^Bearer\s+(\S+)$/i", $header, $matches)) {
            throw new HttpUnauthorizedException($request, "Missing or invalid Authorization header");
        }

        $token = $matches[1];
        $email = $this->tokenAuth->getUserEmail($token);

        if ($email === null) {
            throw new HttpUnauthorizedException($request, "Invalid or expired token");
        }

        return $handler->handle($request->withAttribute(self::REQUEST_ATTRIBUTE, $email));
    }
}
