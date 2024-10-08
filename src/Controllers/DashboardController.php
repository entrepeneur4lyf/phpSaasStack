<?php

namespace Src\Controllers;

use Src\Core\TwigRenderer;
use Src\Services\DashboardService;
use Src\Services\ActivityService;
use Src\Services\AuthService;
use Swoole\Http\Request;
use Swoole\Http\Response;

class DashboardController extends BaseController
{
    protected DashboardService $dashboardService;
    protected ActivityService $activityService;
    protected AuthService $authService;

    public function __construct(
        TwigRenderer $twigRenderer,
        DashboardService $dashboardService,
        ActivityService $activityService,
        AuthService $authService
    ) {
        parent::__construct($twigRenderer);
        $this->dashboardService = $dashboardService;
        $this->activityService = $activityService;
        $this->authService = $authService;
    }

    public function index(Request $request, Response $response): void
    {
        $user = $this->authService->getUser();
        $stats = $this->dashboardService->getStats($user->id);
        $recentActivity = $this->activityService->getRecentActivity($user->id);

        $this->render($response, 'dashboard/dashboard', [
            'user' => $user,
            'stats' => $stats,
            'recentActivity' => $recentActivity
        ]);
    }

    public function getStats(Request $request, Response $response): void
    {
        $user = $this->authService->getUser();
        $stats = $this->dashboardService->getStats($user->id);

        $this->jsonResponse($response, $stats);
    }

    public function getRecentActivity(Request $request, Response $response): void
    {
        $user = $this->authService->getUser();
        $recentActivity = $this->activityService->getRecentActivity($user->id);

        $this->jsonResponse($response, $recentActivity);
    }
}
