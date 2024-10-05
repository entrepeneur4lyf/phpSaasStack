<?php

namespace App\Controllers;

use Twig\Environment;
use App\Services\DashboardService;
use App\Services\ActivityService;
use App\Services\AuthService;

class DashboardController extends BaseController
{
    protected $twig;
    protected $dashboardService;
    protected $activityService;
    protected $authService;

    public function __construct(
        Environment $twig,
        DashboardService $dashboardService,
        ActivityService $activityService,
        AuthService $authService
    ) {
        $this->twig = $twig;
        $this->dashboardService = $dashboardService;
        $this->activityService = $activityService;
        $this->authService = $authService;
    }

    public function index()
    {
        $user = $this->authService->getUser();
        $stats = $this->dashboardService->getStats($user->id);
        $recentActivity = $this->activityService->getRecentActivity($user->id);

        return $this->twig->render('dashboard/dashboard.twig', [
            'user' => $user,
            'stats' => $stats,
            'recentActivity' => $recentActivity
        ]);
    }

    public function getStats()
    {
        $user = $this->authService->getUser();
        $stats = $this->dashboardService->getStats($user->id);

        return $this->response->setJSON($stats);
    }

    public function getRecentActivity()
    {
        $user = $this->authService->getUser();
        $recentActivity = $this->activityService->getRecentActivity($user->id);

        return $this->response->setJSON($recentActivity);
    }
}