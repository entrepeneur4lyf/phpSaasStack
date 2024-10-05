<?php

declare(strict_types=1);

namespace App\Controllers;

use Twig\Environment;
use App\Services\ModerationService;

class ModerationController extends BaseController
{
    protected $twig;
    protected $moderationService;

    public function __construct(Environment $twig, ModerationService $moderationService)
    {
        $this->twig = $twig;
        $this->moderationService = $moderationService;
    }

    public function dashboard()
    {
        $reportedContent = $this->moderationService->getReportedContent();
        $pendingReviews = $this->moderationService->getPendingReviews();
        $moderationLogs = $this->moderationService->getModerationLogs();

        return $this->twig->render('moderation/dashboard.twig', [
            'reported_content' => $reportedContent,
            'pending_reviews' => $pendingReviews,
            'moderation_logs' => $moderationLogs
        ]);
    }

    public function reviewContent($id)
    {
        $content = $this->moderationService->getContentForReview($id);

        if (!$content) {
            return $this->response->setStatusCode(404)->setBody('Content not found');
        }

        return $this->twig->render('moderation/review.twig', [
            'content' => $content
        ]);
    }

    public function approveContent()
    {
        $id = $this->request->getPost('id');
        $result = $this->moderationService->approveContent($id);

        return $this->response->setJSON([
            'success' => $result,
            'message' => $result ? 'Content approved' : 'Failed to approve content'
        ]);
    }

    public function rejectContent()
    {
        $id = $this->request->getPost('id');
        $reason = $this->request->getPost('reason');
        $result = $this->moderationService->rejectContent($id, $reason);

        return $this->response->setJSON([
            'success' => $result,
            'message' => $result ? 'Content rejected' : 'Failed to reject content'
        ]);
    }

    public function removeContent()
    {
        $id = $this->request->getPost('id');
        $result = $this->moderationService->removeContent($id);

        return $this->response->setJSON([
            'success' => $result,
            'message' => $result ? 'Content removed' : 'Failed to remove content'
        ]);
    }
}