<?php

namespace Src\Controllers;

use Src\Core\TwigRenderer;
use Src\Services\LicenseService;
use Src\Services\AuthService;
use Swoole\Http\Request;
use Swoole\Http\Response;

class LicenseController extends BaseController
{
    protected LicenseService $licenseService;
    protected AuthService $authService;

    public function __construct(TwigRenderer $twigRenderer, LicenseService $licenseService, AuthService $authService)
    {
        parent::__construct($twigRenderer);
        $this->licenseService = $licenseService;
        $this->authService = $authService;
    }

    public function index(Request $request, Response $response): void
    {
        $user = $this->authService->getUser();
        $licenses = $this->licenseService->getLicensesByUser($user->id);
        $this->render($response, 'license/index', ['licenses' => $licenses]);
    }

    public function create(Request $request, Response $response): void
    {
        $this->render($response, 'license/create');
    }

    public function store(Request $request, Response $response): void
    {
        $user = $this->authService->getUser();
        $data = $request->post;
        $data['user_id'] = $user->id;

        $result = $this->licenseService->createLicense($data);

        if ($result) {
            $this->jsonResponse($response, ['success' => true, 'message' => 'License created successfully']);
        } else {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to create license'], 500);
        }
    }

    public function edit(Request $request, Response $response, array $args): void
    {
        $user = $this->authService->getUser();
        $license = $this->licenseService->getLicenseById($args['id']);

        if (!$license || $license->user_id !== $user->id) {
            $this->jsonResponse($response, ['error' => 'License not found'], 404);
            return;
        }

        $this->render($response, 'license/edit', ['license' => $license]);
    }

    public function update(Request $request, Response $response, array $args): void
    {
        $user = $this->authService->getUser();
        $license = $this->licenseService->getLicenseById($args['id']);

        if (!$license || $license->user_id !== $user->id) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'License not found'], 404);
            return;
        }

        $data = $request->post;
        $result = $this->licenseService->updateLicense($args['id'], $data);

        if ($result) {
            $this->jsonResponse($response, ['success' => true, 'message' => 'License updated successfully']);
        } else {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to update license'], 500);
        }
    }

    public function delete(Request $request, Response $response, array $args): void
    {
        $user = $this->authService->getUser();
        $license = $this->licenseService->getLicenseById($args['id']);

        if (!$license || $license->user_id !== $user->id) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'License not found'], 404);
            return;
        }

        $result = $this->licenseService->deleteLicense($args['id']);

        if ($result) {
            $this->jsonResponse($response, ['success' => true, 'message' => 'License deleted successfully']);
        } else {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to delete license'], 500);
        }
    }
}