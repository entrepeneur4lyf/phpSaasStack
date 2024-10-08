<?php

namespace Src\Controllers;

use Src\Core\TwigRenderer;
use Src\Services\UserService;
use Src\Services\AuthService;
use Swoole\Http\Request;
use Swoole\Http\Response;

class UserController extends BaseController
{
    protected UserService $userService;
    protected AuthService $authService;

    public function __construct(TwigRenderer $twigRenderer, UserService $userService, AuthService $authService)
    {
        parent::__construct($twigRenderer);
        $this->userService = $userService;
        $this->authService = $authService;
    }

    public function index(Request $request, Response $response): void
    {
        $users = $this->userService->getAllUsers();
        $this->render($response, 'user/index', ['users' => $users]);
    }

    public function profile(Request $request, Response $response, array $args): void
    {
        $user = $this->userService->getUserById($args['id']);
        if (!$user) {
            $this->jsonResponse($response, ['error' => 'User not found'], 404);
            return;
        }
        $this->render($response, 'user/profile', ['user' => $user]);
    }

    public function edit(Request $request, Response $response, array $args): void
    {
        $currentUser = $this->authService->getUser();
        if ($currentUser->id != $args['id'] && !$currentUser->isAdmin()) {
            $this->jsonResponse($response, ['error' => 'Unauthorized'], 403);
            return;
        }

        $user = $this->userService->getUserById($args['id']);
        if (!$user) {
            $this->jsonResponse($response, ['error' => 'User not found'], 404);
            return;
        }

        $this->render($response, 'user/edit', ['user' => $user]);
    }

    public function update(Request $request, Response $response, array $args): void
    {
        $currentUser = $this->authService->getUser();
        if ($currentUser->id != $args['id'] && !$currentUser->isAdmin()) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Unauthorized'], 403);
            return;
        }

        $data = $request->post;
        $result = $this->userService->updateUser($args['id'], $data);

        if ($result) {
            $this->jsonResponse($response, ['success' => true, 'message' => 'User updated successfully']);
        } else {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to update user'], 500);
        }
    }

    public function delete(Request $request, Response $response, array $args): void
    {
        $currentUser = $this->authService->getUser();
        if (!$currentUser->isAdmin()) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Unauthorized'], 403);
            return;
        }

        $result = $this->userService->deleteUser($args['id']);

        if ($result) {
            $this->jsonResponse($response, ['success' => true, 'message' => 'User deleted successfully']);
        } else {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to delete user'], 500);
        }
    }

    public function create(Request $request, Response $response): void
    {
        $this->render($response, 'user/create');
    }

    public function store(Request $request, Response $response): void
    {
        $data = $request->post;
        $result = $this->userService->createUser($data);

        if ($result) {
            $this->jsonResponse($response, ['success' => true, 'message' => 'User created successfully']);
        } else {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to create user'], 500);
        }
    }
}
