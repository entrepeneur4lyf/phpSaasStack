<?php

declare(strict_types=1);

namespace Src\Services;

use Src\Interfaces\CommentServiceInterface;
use Src\Interfaces\ModerationServiceInterface;
use Src\Models\Comment;

class CommentService implements CommentServiceInterface
{
    public function __construct(
        private readonly Comment $commentModel,
        private readonly ModerationServiceInterface $moderationService
    ) {
    }

    public function getThreadedComments(int $postId): array
    {
        $comments = $this->commentModel->getByPostId($postId);
        return $this->buildThreads($comments);
    }

    public function vote(int $id, string $type): array
    {
        $comment = $this->commentModel->findById($id);
        if (!$comment) {
            throw new \RuntimeException('Comment not found');
        }

        $voteField = $type === 'up' ? 'upvotes' : 'downvotes';
        $success = $this->commentModel->incrementVote($id, $voteField);

        if ($success) {
            $updatedComment = $this->commentModel->findById($id);
            return ['success' => true, 'newCount' => $updatedComment[$voteField]];
        } else {
            throw new \RuntimeException('Failed to update vote');
        }
    }

    public function create(array $data): bool
    {
        $commentId = $this->commentModel->create($data);

        if ($commentId) {
            // Submit the comment for moderation
            $this->moderationService->submitForModeration('comment', $commentId);
            return true;
        }

        return false;
    }

    public function update(int $id, array $data): bool
    {
        $result = $this->commentModel->update($id, $data);

        if ($result) {
            // Re-submit the updated comment for moderation
            $this->moderationService->submitForModeration('comment', $id);
        }

        return $result;
    }

    public function delete(int $id): bool
    {
        return $this->commentModel->delete($id);
    }

    private function buildThreads(array $comments, ?int $parentId = null): array
    {
        $threads = [];

        foreach ($comments as $comment) {
            if ($comment['parent_id'] === $parentId) {
                $comment['replies'] = $this->buildThreads($comments, $comment['id']);
                $threads[] = $comment;
            }
        }

        return $threads;
    }
}
