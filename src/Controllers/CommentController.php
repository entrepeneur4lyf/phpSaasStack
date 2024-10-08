<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Interfaces\CommentServiceInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Src\Exceptions\ValidationException;

class CommentController extends BaseController
{
    public function __construct(
        private readonly CommentServiceInterface $commentService
    ) {
    }

    public function create(Request $request, Response $response): void
    {
        try {
            $data = $request->post;
            $userId = $request->user->id; // Assuming user is set by middleware

            $data['user_id'] = $userId;
            $result = $this->commentService->create($data);

            if ($result) {
                $this->jsonResponse($response, ['success' => true, 'message' => 'Comment created successfully']);
            } else {
                $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to create comment'], 400);
            }
        } catch (ValidationException $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    public function edit(Request $request, Response $response, array $args): void
    {
        try {
            $commentId = (int)$args['id'];
            $data = $request->post;
            $userId = $request->user->id; // Assuming user is set by middleware

            $data['user_id'] = $userId;
            $result = $this->commentService->update($commentId, $data);

            if ($result) {
                $this->jsonResponse($response, ['success' => true, 'message' => 'Comment updated successfully']);
            } else {
                $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to update comment'], 400);
            }
        } catch (ValidationException $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    public function delete(Request $request, Response $response, array $args): void
    {
        try {
            $commentId = (int)$args['id'];
            $result = $this->commentService->delete($commentId);

            if ($result) {
                $this->jsonResponse($response, ['success' => true, 'message' => 'Comment deleted successfully']);
            } else {
                $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to delete comment'], 400);
            }
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    public function vote(Request $request, Response $response, array $args): void
    {
        try {
            $commentId = (int)$args['id'];
            $voteType = $args['type'];

            $result = $this->commentService->vote($commentId, $voteType);
            $this->jsonResponse($response, $result);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function getThreadedComments(Request $request, Response $response, array $args): void
    {
        try {
            $postId = (int)$args['postId'];
            $comments = $this->commentService->getThreadedComments($postId);
            $this->jsonResponse($response, ['success' => true, 'comments' => $comments]);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred'], 500);
        }
    }
}
