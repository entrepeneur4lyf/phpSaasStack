<?php

namespace App\Controllers;

use Twig\Environment;
use App\Services\UserService;
use App\Services\AuthService;

class UserController extends BaseController
{
    protected $twig;
    protected $userService;
    protected $authService;

    public function __construct(Environment $twig, UserService $userService, AuthService $authService)
    {
        $this->twig = $twig;
        $this->userService = $userService;
        $this->authService = $authService;
    }

    public function index()
    {
        $users = $this->userService->getAllUsers();
        return $this->twig->render('user/index.twig', ['users' => $users]);
    }

    public function profile($id)
    {
        $user = $this->userService->getUserById($id);
        if (!$user) {
            return $this->response->setStatusCode(404)->setBody('User not found');
        }
        return $this->twig->render('user/profile.twig', ['user' => $user]);
    }

    public function edit($id)
    {
        $currentUser = $this->authService->getUser();
        if ($currentUser->id != $id && !$currentUser->isAdmin()) {
            return $this->response->setStatusCode(403)->setBody('Unauthorized');
        }

        $user = $this->userService->getUserById($id);
        if (!$user) {
            return $this->response->setStatusCode(404)->setBody('User not found');
        }

        return $this->twig->render('user/edit.twig', ['user' => $user]);
    }

    public function update($id)
    {
        $currentUser = $this->authService->getUser();
        if ($currentUser->id != $id && !$currentUser->isAdmin()) {
            return $this->response->setStatusCode(403)->setBody('Unauthorized');
        }

        $data = $this->request->getPost();
        $result = $this->userService->updateUser($id, $data);

        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => 'User updated successfully']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Failed to update user']);
        }
    }
}