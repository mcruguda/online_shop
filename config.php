<?php

return [
    "db" => [
        "host" => getenv("DB_HOST") ?: "127.0.0.1",
        "port" => getenv("DB_PORT") ?: "3306",
        "name" => getenv("DB_NAME") ?: "lb1_uek295",
        "user" => getenv("DB_USER") ?: "root",
        "password" => getenv("DB_PASSWORD") ?: "",
        "charset" => getenv("DB_CHARSET") ?: "utf8mb4",
    ],
    "jwt_secret" => getenv("JWT_SECRET") ?: "Sec!ReT423SlmApi",
    "jwt_issuer" => getenv("JWT_ISSUER") ?: "online-shop-api",
    "jwt_ttl" => (int) (getenv("JWT_TTL") ?: 3600),
];
