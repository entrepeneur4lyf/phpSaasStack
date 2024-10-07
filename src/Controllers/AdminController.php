<?php

namespace Src\Controllers;

use Src\Core\TwigRenderer;
use Src\Services\AdminService;
use Src\Services\AuthService;
use Swoole\Http\Request;
use Swoole\Http\Response;

class AdminController extends BaseController
{
    protected AdminService $adminService;
    protected AuthService $authService;

    public function __construct(TwigRenderer $twigRenderer, AdminService $adminService, AuthService $authService)
    {
        parent::__construct($twigRenderer);
        $this->adminService = $adminService;
        $this->authService = $authService;
    }

    public function dashboard(Request $request, Response $response): void
    {
        $user = $this->authService->getUser();
        if (!$user->isAdmin()) {
            $this->jsonResponse($response, ['error' => 'Unauthorized'], 403);
            return;
        }

        $stats = $this->adminService->getDashboardStats();
        $this->render($response, 'admin/dashboard', ['stats' => $stats]);
    }

    public function users(Request $request, Response $response): void
    {
        $user = $this->authService->getUser();
        if (!$user->isAdmin()) {
            $this->jsonResponse($response, ['error' => 'Unauthorized'], 403);
            return;
        }

        $users = $this->adminService->getAllUsers();
        $this->render($response, 'admin/users', ['users' => $users]);
    }

    public function editUser(Request $request, Response $response, array $args): void
    {
        $user = $this->authService->getUser();
        if (!$user->isAdmin()) {
            $this->jsonResponse($response, ['error' => 'Unauthorized'], 403);
            return;
        }

        $editUser = $this->adminService->getUserById($args['id']);
        $this->render($response, 'admin/edit_user', ['editUser' => $editUser]);
    }

    public function updateUser(Request $request, Response $response, array $args): void
    {
        $user = $this->authService->getUser();
        if (!$user->isAdmin()) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Unauthorized'], 403);
            return;
        }

        $data = $request->post;
        $result = $this->adminService->updateUser($args['id'], $data);

        if ($result) {
            $this->jsonResponse($response, ['success' => true, 'message' => 'User updated successfully']);
        } else {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to update user'], 500);
        }
    }

    public function messageCategories(Request $request, Response $response): void
    {
        $user = $this->authService->getUser();
        if (!$user->isAdmin()) {
            $this->jsonResponse($response, ['error' => 'Unauthorized'], 403);
            return;
        }

        $categories = $this->adminService->getMessageCategories();
        $this->render($response, 'admin/message_categories', ['categories' => $categories]);
    }

    public function updateMessageCategory(Request $request, Response $response): void
    {
        $user = $this->authService->getUser();
        if (!$user->isAdmin()) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Unauthorized'], 403);
            return;
        }

        $categoryId = $request->post['category_id'];
        $data = $request->post;
        $result = $this->adminService->updateMessageCategory($categoryId, $data);

        if ($result) {
            $this->jsonResponse($response, ['success' => true, 'message' => 'Category updated successfully']);
        } else {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to update category'], 500);
        }
    }
}