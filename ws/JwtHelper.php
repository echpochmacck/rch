<?php

class JwtHelper
{
    public static function decode(string $token): ?array
    {
        try {
            return json_decode(base64_decode($token), true);
        } catch (\Throwable $e) {
            return null;
        }
    }
   
}
