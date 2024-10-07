<?php

namespace Src\Controllers;

use Src\Core\TwigRenderer;
use Src\Services\PortfolioService;
use Src\Services\AuthService;
use Swoole\Http\Request;
use Swoole\Http\Response;

class PortfolioController extends BaseController
{
    protected PortfolioService $portfolioService;
    protected AuthService $authService;

    public function __construct(TwigRenderer $twigRenderer, PortfolioService $portfolioService, AuthService $authService)
    {
        parent::__construct($twigRenderer);
        $this->portfolioService = $portfolioService;
        $this->authService = $authService;
    }

    public function index(Request $request, Response $response, array $args): void
    {
        $userId = $args['userId'] ?? null;
        if ($userId === null) {
            $user = $this->authService->getUser();
            $userId = $user->id;
        }

        $portfolio = $this->portfolioService->getPortfolioByUserId($userId);
        $this->render($response, 'portfolio/index', ['portfolio' => $portfolio]);
    }

    public function edit(Request $request, Response $response): void
    {
        $user = $this->authService->getUser();
        $portfolio = $this->portfolioService->getPortfolioByUserId($user->id);
        $this->render($response, 'portfolio/edit', ['portfolio' => $portfolio]);
    }

    public function update(Request $request, Response $response): void
    {
        $user = $this->authService->getUser();
        $data = $request->post;
        $result = $this->portfolioService->updatePortfolio($user->id, $data);

        if ($result) {
            $this->jsonResponse($response, ['success' => true, 'message' => 'Portfolio updated successfully']);
        } else {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to update portfolio'], 500);
        }
    }

    public function addProject(Request $request, Response $response): void
    {
        $user = $this->authService->getUser();
        $data = $request->post;
        $result = $this->portfolioService->addProject($user->id, $data);

        if ($result) {
            $this->jsonResponse($response, ['success' => true, 'message' => 'Project added successfully']);
        } else {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to add project'], 500);
        }
    }

    public function removeProject(Request $request, Response $response, array $args): void
    {
        $user = $this->authService->getUser();
        $projectId = $args['projectId'] ?? null;
        $result = $this->portfolioService->removeProject($user->id, $projectId);

        if ($result) {
            $this->jsonResponse($response, ['success' => true, 'message' => 'Project removed successfully']);
        } else {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to remove project'], 500);
        }
    }
}