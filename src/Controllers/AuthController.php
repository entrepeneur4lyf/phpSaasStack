<?php

namespace App\Controllers;

use Twig\Environment;
use App\Services\AuthService;

class AuthController extends BaseController
{
    protected $twig;
    protected $authService;

    public function __construct(Environment $twig, AuthService $authService)
    {
        $this->twig = $twig;
        $this->authService = $authService;
    }

    public function login()
    {
        if ($this->request->getMethod() === 'post') {
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');

            $result = $this->authService->login($email, $password);

            if ($result['success']) {
                return redirect()->to('/dashboard');
            } else {
                return $this->twig->render('auth/login.twig', ['error' => $result['message']]);
            }
        }

        return $this->twig->render('auth/login.twig');
    }

    public function register()
    {
        if ($this->request->getMethod() === 'post') {
            $data = $this->request->getPost();
            $result = $this->authService->register($data);

            if ($result['success']) {
                return redirect()->to('/login')->with('success', 'Registration successful. Please login.');
            } else {
                return $this->twig->render('auth/register.twig', ['error' => $result['message'], 'data' => $data]);
            }
        }

        return $this->twig->render('auth/register.twig');
    }

    public function logout()
    {
        $this->authService->logout();
        return redirect()->to('/login');
    }

    public function forgotPassword()
    {
        if ($this->request->getMethod() === 'post') {
            $email = $this->request->getPost('email');
            $result = $this->authService->sendPasswordResetLink($email);

            if ($result['success']) {
                return $this->twig->render('auth/forgot_password.twig', ['success' => $result['message']]);
            } else {
                return $this->twig->render('auth/forgot_password.twig', ['error' => $result['message']]);
            }
        }

        return $this->twig->render('auth/forgot_password.twig');
    }

    public function resetPassword($token)
    {
        if ($this->request->getMethod() === 'post') {
            $password = $this->request->getPost('password');
            $result = $this->authService->resetPassword($token, $password);

            if ($result['success']) {
                return redirect()->to('/login')->with('success', 'Password reset successful. Please login.');
            } else {
                return $this->twig->render('auth/reset_password.twig', ['error' => $result['message'], 'token' => $token]);
            }
        }

        return $this->twig->render('auth/reset_password.twig', ['token' => $token]);
    }
}