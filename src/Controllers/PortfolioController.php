<?php

namespace App\Controllers;

use Twig\Environment;
use App\Services\PortfolioService;
use App\Services\AuthService;

class PortfolioController extends BaseController
{
    protected $twig;
    protected $portfolioService;
    protected $authService;

    public function __construct(Environment $twig, PortfolioService $portfolioService, AuthService $authService)
    {
        $this->twig = $twig;
        $this->portfolioService = $portfolioService;
        $this->authService = $authService;
    }

    public function index($userId = null)
    {
        if ($userId === null) {
            $user = $this->authService->getUser();
            $userId = $user->id;
        }

        $portfolio = $this->portfolioService->getPortfolioByUserId($userId);
        return $this->twig->render('portfolio/index.twig', ['portfolio' => $portfolio]);
    }

    public function edit()
    {
        $user = $this->authService->getUser();
        $portfolio = $this->portfolioService->getPortfolioByUserId($user->id);
        return $this->twig->render('portfolio/edit.twig', ['portfolio' => $portfolio]);
    }

    public function update()
    {
        $user = $this->authService->getUser();
        $data = $this->request->getPost();
        $result = $this->portfolioService->updatePortfolio($user->id, $data);

        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => 'Portfolio updated successfully']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Failed to update portfolio']);
        }
    }

    public function addProject()
    {
        $user = $this->authService->getUser();
        $data = $this->request->getPost();
        $result = $this->portfolioService->addProject($user->id, $data);

        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => 'Project added successfully']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Failed to add project']);
        }
    }

    public function removeProject($projectId)
    {
        $user = $this->authService->getUser();
        $result = $this->portfolioService->removeProject($user->id, $projectId);

        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => 'Project removed successfully']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Failed to remove project']);
        }
    }
}