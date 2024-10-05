<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Interfaces\UserServiceInterface;
use Src\Interfaces\EmailServiceInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;

class UserController
{
    public function __construct(
        private readonly UserServiceInterface $userService,
        private readonly EmailServiceInterface $emailService
    ) {}

    public function register(Request $request, Response $response): void
    {
        try {
            $data = json_decode($request->getContent(), true);
            $user = $this->userService->createUser($data);
            $this->emailService->sendVerificationEmail($user);
            
            $this->jsonResponse($response, ['message' => 'Registration successful. Please check your email to verify your account.'], 201);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['error' => $e->getMessage()], 400);
        }
    }

    public function login(Request $request, Response $response): void
    {
        try {
            $data = json_decode($request->getContent(), true);
            $token = $this->userService->loginUser($data['email'], $data['password']);
            
            $this->jsonResponse($response, ['token' => $token]);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['error' => $e->getMessage()], 401);
        }
    }

    public function getProfile(Request $request, Response $response): void
    {
        try {
            $userId = $request->user->id; // Assuming middleware sets the user
            $user = $this->userService->getUserById($userId);
            
            $this->jsonResponse($response, ['user' => $user]);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['error' => $e->getMessage()], 400);
        }
    }

    public function updateProfile(Request $request, Response $response): void
    {
        try {
            $userId = $request->user->id; // Assuming middleware sets the user
            $data = json_decode($request->getContent(), true);
            $updatedUser = $this->userService->updateUser($userId, $data);
            
            $this->jsonResponse($response, ['user' => $updatedUser]);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['error' => $e->getMessage()], 400);
        }
    }

    public function deleteUser(Request $request, Response $response, array $args): void
    {
        try {
            $userId = (int) $args['id'];
            $this->userService->deleteUser($userId);
            
            $this->jsonResponse($response, ['message' => 'User deleted successfully']);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['error' => $e->getMessage()], 400);
        }
    }

    private function jsonResponse(Response $response, array $data, int $statusCode = 200): void
    {
        $response->status($statusCode);
        $response->header('Content-Type', 'application/json');
        $response->end(json_encode($data));
    }
}