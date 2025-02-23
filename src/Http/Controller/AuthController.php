<?php

namespace App\Http\Controller;

use App\Application\Service\AuthService;
use App\Application\Service\JWTService;
use App\Application\Exception\ValidationException;
use App\Application\Exception\AuthenticationException;

class AuthController extends AbstractController
{
    private AuthService $authService;
    private JWTService $jwtService;

    public function __construct(AuthService $authService, JWTService $jwtService)
    {
        $this->authService = $authService;
        $this->jwtService = $jwtService;
    }

    public function register(): void
    {
        try {
            $userData = $this->getRequestData();

            $user = $this->authService->register($userData);

            // Используем JWTService для создания токена
            $token = $this->jwtService->createToken($user);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Registration successful',
                'token' => $token,
                'user' => [
                    'id' => $user->getId(),
                    'nickname' => $user->getNickname(),
                    'email' => $user->getEmail(),
                    'role' => $user->getRole()
                ]
            ]);
        } catch (ValidationException $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    public function login(): void
    {
        try {
            $data = $this->getRequestData();

            if (empty($data['email']) || empty($data['password'])) {
                throw new ValidationException('Email and password are required');
            }

            $user = $this->authService->login($data['email'], $data['password']);

            // Используем JWTService для создания токена
            $token = $this->jwtService->createToken($user);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Login successful',
                'token' => $token,
                'user' => [
                    'id' => $user->getId(),
                    'nickname' => $user->getNickname(),
                    'email' => $user->getEmail(),
                    'role' => $user->getRole()
                ]
            ]);
        } catch (AuthenticationException $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}