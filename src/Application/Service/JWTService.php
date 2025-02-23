<?php

namespace App\Application\Service;

use App\Domain\Entity\User;

class JWTService
{
    private string $secret;
    private int $expirationTime;

    public function __construct(string $secret = null, int $expirationTimeInHours = 24)
    {
        if ($secret === null) {
            $secret = bin2hex(random_bytes(32));
        }

        $this->secret = $secret;
        $this->expirationTime = $expirationTimeInHours * 3600;
    }

    public function createToken(User $user): string
    {
        $header = json_encode([
            'typ' => 'JWT',
            'alg' => 'HS256'
        ]);

        $payload = json_encode([
            'user_id' => $user->getId(),
            'email' => $user->getEmail(),
            'role' => $user->getRole(),
            'exp' => time() + $this->expirationTime,
            'iat' => time()
        ]);

        $base64Header = $this->base64UrlEncode($header);
        $base64Payload = $this->base64UrlEncode($payload);

        $signature = hash_hmac('sha256',
            $base64Header . "." . $base64Payload,
            $this->secret,
            true
        );

        $base64Signature = $this->base64UrlEncode($signature);

        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }

    private function base64UrlEncode(string $data): string
    {
        return str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode($data)
        );
    }
}