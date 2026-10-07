<?php

namespace App;

use ReallySimpleJWT\Token;

class TokenAuth
{
    private string $secret;

    private string $issuer;

    /** Token lifetime in seconds. */
    private int $ttl;

    public function __construct(string $secret, string $issuer = "slim-person-api", int $ttl = 3600)
    {
        $this->secret = $secret;
        $this->issuer = $issuer;
        $this->ttl = $ttl;
    }

    public function createForUser(string $email): string
    {
        $now = time();

        return Token::customPayload([
            "iat" => $now,
            "exp" => $now + $this->ttl,
            "iss" => $this->issuer,
            "uid" => strtolower($email),
        ], $this->secret);
    }

    public function validate(string $token): bool
    {
        if (!Token::validate($token, $this->secret)) {
            return false;
        }

        return Token::validateExpiration($token);
    }

    public function getTtl(): int
    {
        return $this->ttl;
    }

    public function getUserEmail(string $token): ?string
    {
        if (!$this->validate($token)) {
            return null;
        }

        $payload = Token::getPayload($token);
        $uid = $payload["uid"] ?? null;

        return is_string($uid) ? $uid : null;
    }
}
