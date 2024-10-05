<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Interfaces\AuthServiceInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Src\Exceptions\ValidationException;

class AuthController extends BaseController
{
    public function __construct(
        private readonly AuthServiceInterface $authService
    ) {}

    public function showLoginForm(Request $request, Response $response): void
    {
        $this->render($response, 'auth/login');
    }

    public function login(Request $request, Response $response): void
    {
        try {
            $credentials = [
                'email' => $request->post['email'],
                'password' => $request->post['password']
            ];

            $token = $this->authService->login($credentials);

            if ($token) {
                $response->cookie('auth_token', $token, time() + 86400, '/', '', true, true);
                $this->redirect($response, '/dashboard');
            } else {
                $this->render($response, 'auth/login', ['error' => 'Invalid credentials']);
            }
        } catch (ValidationException $e) {
            $this->render($response, 'auth/login', ['error' => $e->getMessage()]);
        }
    }

    public function showRegistrationForm(Request $request, Response $response): void
    {
        $this->render($response, 'auth/register');
    }

    public function register(Request $request, Response $response): void
    {
        try {
            $userData = [
                'name' => $request->post['name'],
                'email' => $request->post['email'],
                'password' => $request->post['password']
            ];

            $user = $this->authService->register($userData);

            if ($user) {
                $this->redirect($response, '/login');
            } else {
                $this->render($response, 'auth/register', ['error' => 'Registration failed']);
            }
        } catch (ValidationException $e) {
            $this->render($response, 'auth/register', ['error' => $e->getMessage()]);
        }
    }

    public function logout(Request $request, Response $response): void
    {
        $this->authService->logout();
        $response->cookie('auth_token', '', time() - 3600, '/', '', true, true);
        $this->redirect($response, '/');
    }

    public function showForgotPasswordForm(Request $request, Response $response): void
    {
        $this->render($response, 'auth/forgot-password');
    }

    public function forgotPassword(Request $request, Response $response): void
    {
        try {
            $email = $request->post['email'];
            $result = $this->authService->sendPasswordResetLink($email);

            if ($result) {
                $this->render($response, 'auth/forgot-password', ['success' => 'Password reset link sent to your email']);
            } else {
                $this->render($response, 'auth/forgot-password', ['error' => 'Failed to send password reset link']);
            }
        } catch (ValidationException $e) {
            $this->render($response, 'auth/forgot-password', ['error' => $e->getMessage()]);
        }
    }

    public function showResetPasswordForm(Request $request, Response $response, array $args): void
    {
        $token = $args['token'];
        $this->render($response, 'auth/reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request, Response $response): void
    {
        try {
            $token = $request->post['token'];
            $password = $request->post['password'];
            $result = $this->authService->resetPassword($token, $password);

            if ($result) {
                $this->redirect($response, '/login');
            } else {
                $this->render($response, 'auth/reset-password', ['error' => 'Failed to reset password']);
            }
        } catch (ValidationException $e) {
            $this->render($response, 'auth/reset-password', ['error' => $e->getMessage()]);
        }
    }
}