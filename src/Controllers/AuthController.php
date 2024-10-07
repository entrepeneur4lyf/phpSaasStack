<?php

namespace Src\Controllers;

use Src\Core\TwigRenderer;
use Src\Services\AuthService;
use Swoole\Http\Request;
use Swoole\Http\Response;

class AuthController extends BaseController
{
    protected AuthService $authService;

    public function __construct(TwigRenderer $twigRenderer, AuthService $authService)
    {
        parent::__construct($twigRenderer);
        $this->authService = $authService;
    }

    public function login(Request $request, Response $response): void
    {
        if ($request->getMethod() === 'POST') {
            $email = $request->post['email'] ?? '';
            $password = $request->post['password'] ?? '';

            try {
                $token = $this->authService->login($email, $password);
                $this->jsonResponse($response, ['success' => true, 'token' => $token]);
            } catch (\Exception $e) {
                $this->jsonResponse($response, ['success' => false, 'message' => $e->getMessage()], 401);
            }
        } else {
            $this->render($response, 'auth/login');
        }
    }

    public function register(Request $request, Response $response): void
    {
        if ($request->getMethod() === 'POST') {
            $data = $request->post;
            try {
                $result = $this->authService->register($data);
                if ($result['success']) {
                    $this->jsonResponse($response, ['success' => true, 'message' => 'Registration successful. Please login.']);
                } else {
                    $this->jsonResponse($response, ['success' => false, 'message' => $result['message']], 400);
                }
            } catch (\Exception $e) {
                $this->jsonResponse($response, ['success' => false, 'message' => $e->getMessage()], 400);
            }
        } else {
            $this->render($response, 'auth/register');
        }
    }

    public function logout(Request $request, Response $response): void
    {
        $this->authService->logout();
        $this->jsonResponse($response, ['success' => true, 'message' => 'Logged out successfully']);
    }

    public function forgotPassword(Request $request, Response $response): void
    {
        if ($request->getMethod() === 'POST') {
            $email = $request->post['email'] ?? '';
            $result = $this->authService->sendPasswordResetLink($email);

            if ($result['success']) {
                $this->jsonResponse($response, ['success' => true, 'message' => $result['message']]);
            } else {
                $this->jsonResponse($response, ['success' => false, 'message' => $result['message']], 400);
            }
        } else {
            $this->render($response, 'auth/forgot_password');
        }
    }

    public function resetPassword(Request $request, Response $response, array $args): void
    {
        $token = $args['token'] ?? '';

        if ($request->getMethod() === 'POST') {
            $password = $request->post['password'] ?? '';
            $result = $this->authService->resetPassword($token, $password);

            if ($result['success']) {
                $this->jsonResponse($response, ['success' => true, 'message' => 'Password reset successful. Please login.']);
            } else {
                $this->jsonResponse($response, ['success' => false, 'message' => $result['message']], 400);
            }
        } else {
            $this->render($response, 'auth/reset_password', ['token' => $token]);
        }
    }
}