<?php

namespace App;

final class JsonResponse
{
    /**
     * @param array<string, mixed>|list<mixed> $data
     */
    public static function encode(array $data): string
    {
        return json_encode(
            $data,
            JSON_UNESCAPED_UNICODE
                | JSON_PRETTY_PRINT
                | JSON_HEX_TAG
                | JSON_HEX_AMP
                | JSON_HEX_APOS
                | JSON_HEX_QUOT
        ) ?: "{}";
    }
}
