<?php

namespace App\Controllers;

use Twig\Environment;
use App\Services\LicenseService;
use App\Services\AuthService;

class LicenseController extends BaseController
{
    protected $twig;
    protected $licenseService;
    protected $authService;

    public function __construct(Environment $twig, LicenseService $licenseService, AuthService $authService)
    {
        $this->twig = $twig;
        $this->licenseService = $licenseService;
        $this->authService = $authService;
    }

    public function index()
    {
        $user = $this->authService->getUser();
        $licenses = $this->licenseService->getLicensesByUser($user->id);
        return $this->twig->render('license/index.twig', ['licenses' => $licenses]);
    }

    public function create()
    {
        return $this->twig->render('license/create.twig');
    }

    public function store()
    {
        $user = $this->authService->getUser();
        $data = $this->request->getPost();
        $data['user_id'] = $user->id;

        $result = $this->licenseService->createLicense($data);

        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => 'License created successfully']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Failed to create license']);
        }
    }

    public function edit($id)
    {
        $user = $this->authService->getUser();
        $license = $this->licenseService->getLicenseById($id);

        if (!$license || $license->user_id !== $user->id) {
            return $this->response->setStatusCode(404)->setBody('License not found');
        }

        return $this->twig->render('license/edit.twig', ['license' => $license]);
    }

    public function update($id)
    {
        $user = $this->authService->getUser();
        $license = $this->licenseService->getLicenseById($id);

        if (!$license || $license->user_id !== $user->id) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'License not found']);
        }

        $data = $this->request->getPost();
        $result = $this->licenseService->updateLicense($id, $data);

        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => 'License updated successfully']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Failed to update license']);
        }
    }

    public function delete($id)
    {
        $user = $this->authService->getUser();
        $license = $this->licenseService->getLicenseById($id);

        if (!$license || $license->user_id !== $user->id) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'License not found']);
        }

        $result = $this->licenseService->deleteLicense($id);

        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => 'License deleted successfully']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Failed to delete license']);
        }
    }
}