<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Core\TwigRenderer;
use Src\Services\ModerationService;
use Src\Services\AuthService;
use Swoole\Http\Request;
use Swoole\Http\Response;

class ModerationController extends BaseController
{
    protected ModerationService $moderationService;
    protected AuthService $authService;

    public function __construct(TwigRenderer $twigRenderer, ModerationService $moderationService, AuthService $authService)
    {
        parent::__construct($twigRenderer);
        $this->moderationService = $moderationService;
        $this->authService = $authService;
    }

    public function dashboard(Request $request, Response $response): void
    {
        $user = $this->authService->getUser();
        if (!$user->isAdmin()) {
            $this->jsonResponse($response, ['error' => 'Unauthorized'], 403);
            return;
        }

        $reportedContent = $this->moderationService->getReportedContent();
        $pendingReviews = $this->moderationService->getPendingReviews();
        $moderationLogs = $this->moderationService->getModerationLogs();

        $this->render($response, 'moderation/dashboard', [
            'reported_content' => $reportedContent,
            'pending_reviews' => $pendingReviews,
            'moderation_logs' => $moderationLogs
        ]);
    }

    public function reviewContent(Request $request, Response $response, array $args): void
    {
        $user = $this->authService->getUser();
        if (!$user->isAdmin()) {
            $this->jsonResponse($response, ['error' => 'Unauthorized'], 403);
            return;
        }

        $content = $this->moderationService->getContentForReview($args['id']);

        if (!$content) {
            $this->jsonResponse($response, ['error' => 'Content not found'], 404);
            return;
        }

        $this->render($response, 'moderation/review', [
            'content' => $content
        ]);
    }

    public function approveContent(Request $request, Response $response): void
    {
        $user = $this->authService->getUser();
        if (!$user->isAdmin()) {
            $this->jsonResponse($response, ['error' => 'Unauthorized'], 403);
            return;
        }

        $id = $request->post['id'] ?? null;
        $result = $this->moderationService->approveContent($id);

        $this->jsonResponse($response, [
            'success' => $result,
            'message' => $result ? 'Content approved' : 'Failed to approve content'
        ]);
    }

    public function rejectContent(Request $request, Response $response): void
    {
        $user = $this->authService->getUser();
        if (!$user->isAdmin()) {
            $this->jsonResponse($response, ['error' => 'Unauthorized'], 403);
            return;
        }

        $id = $request->post['id'] ?? null;
        $reason = $request->post['reason'] ?? '';
        $result = $this->moderationService->rejectContent($id, $reason);

        $this->jsonResponse($response, [
            'success' => $result,
            'message' => $result ? 'Content rejected' : 'Failed to reject content'
        ]);
    }

    public function removeContent(Request $request, Response $response): void
    {
        $user = $this->authService->getUser();
        if (!$user->isAdmin()) {
            $this->jsonResponse($response, ['error' => 'Unauthorized'], 403);
            return;
        }

        $id = $request->post['id'] ?? null;
        $result = $this->moderationService->removeContent($id);

        $this->jsonResponse($response, [
            'success' => $result,
            'message' => $result ? 'Content removed' : 'Failed to remove content'
        ]);
    }
}
