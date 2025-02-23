<?php

namespace App\Http\Middleware;

use App\Domain\Repository\UserRepositoryInterface;
use Exception;

class AuthMiddleware
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function handle(): bool
    {
        $token = $this->getBearerToken();

        if (!$token) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'No token provided'
            ]);
            return false;
        }

        try {
            $payload = $this->validateToken($token);

            $user = $this->userRepository->findByEmail($payload['email']);
            if (!$user) {
                throw new Exception('User not found');
            }

            $_REQUEST['user'] = $user;

            return true;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid token'
            ]);
            return false;
        }
    }

    private function getBearerToken(): ?string
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';

        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * @throws Exception
     */
    private function validateToken(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new Exception('Invalid token format');
        }

        $payload = json_decode(base64_decode($parts[1]), true);

        if (!$payload || !isset($payload['exp']) || $payload['exp'] < time()) {
            throw new Exception('Token has expired');
        }

        $secret = $_ENV['JWT_SECRET'];
        $signature = hash_hmac('sha256', $parts[0] . "." . $parts[1], $secret, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        if (!hash_equals($base64Signature, $parts[2])) {
            throw new Exception('Invalid token signature');
        }

        return $payload;
    }
}