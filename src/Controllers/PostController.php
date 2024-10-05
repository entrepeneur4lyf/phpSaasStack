<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Interfaces\PostServiceInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Src\Exceptions\ValidationException;

class PostController extends BaseController
{
    public function __construct(
        private readonly PostServiceInterface $postService
    ) {}

    public function index(Request $request, Response $response): void
    {
        $page = (int) ($request->get['page'] ?? 1);
        $limit = (int) ($request->get['limit'] ?? 20);

        $posts = $this->postService->getPosts($page, $limit);
        $this->jsonResponse($response, ['success' => true, 'posts' => $posts]);
    }

    public function view(Request $request, Response $response, array $args): void
    {
        $postId = (int) $args['id'];
        $post = $this->postService->getPostById($postId);

        if ($post) {
            $this->jsonResponse($response, ['success' => true, 'post' => $post]);
        } else {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Post not found'], 404);
        }
    }

    public function create(Request $request, Response $response): void
    {
        try {
            $postData = $request->post;
            $postData['user_id'] = $request->user->id; // Assuming user is set by middleware

            $postId = $this->postService->createPost($postData);
            $this->jsonResponse($response, ['success' => true, 'message' => 'Post created successfully', 'post_id' => $postId]);
        } catch (ValidationException $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred while creating the post'], 500);
        }
    }

    public function update(Request $request, Response $response, array $args): void
    {
        try {
            $postId = (int) $args['id'];
            $postData = $request->post;

            $success = $this->postService->updatePost($postId, $postData);
            if ($success) {
                $this->jsonResponse($response, ['success' => true, 'message' => 'Post updated successfully']);
            } else {
                $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to update post'], 400);
            }
        } catch (ValidationException $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred while updating the post'], 500);
        }
    }

    public function delete(Request $request, Response $response, array $args): void
    {
        try {
            $postId = (int) $args['id'];
            $success = $this->postService->deletePost($postId);
            if ($success) {
                $this->jsonResponse($response, ['success' => true, 'message' => 'Post deleted successfully']);
            } else {
                $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to delete post'], 400);
            }
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred while deleting the post'], 500);
        }
    }

    public function search(Request $request, Response $response): void
    {
        $filters = $request->get;
        $results = $this->postService->searchPosts($filters);
        $this->jsonResponse($response, ['success' => true, 'results' => $results]);
    }

    public function schedule(Request $request, Response $response, array $args): void
    {
        try {
            $postId = (int) $args['id'];
            $scheduledAt = $request->post['scheduled_at'];
            $success = $this->postService->schedulePost($postId, $scheduledAt);
            if ($success) {
                $this->jsonResponse($response, ['success' => true, 'message' => 'Post scheduled successfully']);
            } else {
                $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to schedule post'], 400);
            }
        } catch (ValidationException $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred while scheduling the post'], 500);
        }
    }

    public function toggleFeatured(Request $request, Response $response, array $args): void
    {
        try {
            $postId = (int) $args['id'];
            $success = $this->postService->toggleFeatured($postId);
            if ($success) {
                $this->jsonResponse($response, ['success' => true, 'message' => 'Post featured status updated successfully']);
            } else {
                $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to update post featured status'], 400);
            }
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred while updating post featured status'], 500);
        }
    }

    public function featuredPosts(Request $request, Response $response): void
    {
        $featuredPosts = $this->postService->getFeaturedPosts();
        $this->jsonResponse($response, ['success' => true, 'featured_posts' => $featuredPosts]);
    }
}