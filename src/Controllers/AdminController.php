<?php

namespace App\Controllers;

use Twig\Environment;
use App\Services\AdminService;
use App\Services\AuthService;

class AdminController extends BaseController
{
    protected $twig;
    protected $adminService;
    protected $authService;

    public function __construct(Environment $twig, AdminService $adminService, AuthService $authService)
    {
        $this->twig = $twig;
        $this->adminService = $adminService;
        $this->authService = $authService;
    }

    public function dashboard()
    {
        $user = $this->authService->getUser();
        if (!$user->isAdmin()) {
            return $this->response->setStatusCode(403)->setBody('Unauthorized');
        }

        $stats = $this->adminService->getDashboardStats();
        return $this->twig->render('admin/dashboard.twig', ['stats' => $stats]);
    }

    public function users()
    {
        $user = $this->authService->getUser();
        if (!$user->isAdmin()) {
            return $this->response->setStatusCode(403)->setBody('Unauthorized');
        }

        $users = $this->adminService->getAllUsers();
        return $this->twig->render('admin/users.twig', ['users' => $users]);
    }

    public function editUser($id)
    {
        $user = $this->authService->getUser();
        if (!$user->isAdmin()) {
            return $this->response->setStatusCode(403)->setBody('Unauthorized');
        }

        $editUser = $this->adminService->getUserById($id);
        return $this->twig->render('admin/edit_user.twig', ['editUser' => $editUser]);
    }

    public function updateUser($id)
    {
        $user = $this->authService->getUser();
        if (!$user->isAdmin()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $data = $this->request->getPost();
        $result = $this->adminService->updateUser($id, $data);

        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => 'User updated successfully']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Failed to update user']);
        }
    }

    public function messageCategories()
    {
        $user = $this->authService->getUser();
        if (!$user->isAdmin()) {
            return $this->response->setStatusCode(403)->setBody('Unauthorized');
        }

        $categories = $this->adminService->getMessageCategories();
        return $this->twig->render('admin/message_categories.twig', ['categories' => $categories]);
    }

    public function updateMessageCategory()
    {
        $user = $this->authService->getUser();
        if (!$user->isAdmin()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $categoryId = $this->request->getPost('category_id');
        $data = $this->request->getPost();
        $result = $this->adminService->updateMessageCategory($categoryId, $data);

        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => 'Category updated successfully']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Failed to update category']);
        }
    }
}