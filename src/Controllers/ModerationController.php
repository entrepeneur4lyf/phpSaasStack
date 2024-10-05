<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Interfaces\ModerationServiceInterface;
use Src\Interfaces\UserServiceInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;

class ModerationController extends BaseController
{
    public function __construct(
        private readonly ModerationServiceInterface $moderationService,
        private readonly UserServiceInterface $userService
    ) {}

    public function index(Request $request, Response $response): void
    {
        if (!$this->moderationService->isModeratorOrAdmin($request->user)) {
            $this->redirect($response, '/');
            return;
        }

        $reports = $this->moderationService->getAllReports();
        $this->render($response, 'moderation/dashboard', ['reports' => $reports]);
    }

    public function reviewReport(Request $request, Response $response, array $args): void
    {
        if (!$this->moderationService->isModeratorOrAdmin($request->user)) {
            $this->redirect($response, '/');
            return;
        }

        $reportId = (int) $args['id'];
        $report = $this->moderationService->getReportById($reportId);
        if (!$report) {
            $this->redirect($response, '/moderation');
            return;
        }

        $reportedContent = $this->moderationService->getReportedContent($report['content_type'], $report['content_id']);
        $reportedUser = $this->userService->getUserById($report['reported_user_id']);

        $this->render($response, 'moderation/review_report', [
            'report' => $report,
            'reportedContent' => $reportedContent,
            'reportedUser' => $reportedUser
        ]);
    }

    public function approveContent(Request $request, Response $response, array $args): void
    {
        if (!$this->moderationService->isModeratorOrAdmin($request->user)) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $reportId = (int) $args['id'];
        $result = $this->moderationService->approveContent($reportId, $request->user->id);
        $this->jsonResponse($response, ['success' => $result]);
    }

    public function removeContent(Request $request, Response $response, array $args): void
    {
        if (!$this->moderationService->isModeratorOrAdmin($request->user)) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $reportId = (int) $args['id'];
        $result = $this->moderationService->removeContent($reportId, $request->user->id);
        $this->jsonResponse($response, ['success' => $result]);
    }

    public function warnUser(Request $request, Response $response, array $args): void
    {
        if (!$this->moderationService->isModeratorOrAdmin($request->user)) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $userId = (int) $args['id'];
        $result = $this->moderationService->warnUser($userId, $request->user->id);
        $this->jsonResponse($response, ['success' => $result]);
    }

    public function suspendUser(Request $request, Response $response, array $args): void
    {
        if (!$this->moderationService->isModeratorOrAdmin($request->user)) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $userId = (int) $args['id'];
        $days = (int) $args['days'];
        $result = $this->moderationService->suspendUser($userId, $days, $request->user->id);
        $this->jsonResponse($response, ['success' => $result]);
    }
}