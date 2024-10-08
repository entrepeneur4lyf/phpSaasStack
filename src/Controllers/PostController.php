<?php

namespace Src\Controllers;

use Src\Core\TwigRenderer;
use Src\Services\PostService;
use Src\Services\AuthService;
use Swoole\Http\Request;
use Swoole\Http\Response;

class PostController extends BaseController
{
    protected PostService $postService;
    protected AuthService $authService;

    public function __construct(TwigRenderer $twigRenderer, PostService $postService, AuthService $authService)
    {
        parent::__construct($twigRenderer);
        $this->postService = $postService;
        $this->authService = $authService;
    }

    public function index(Request $request, Response $response): void
    {
        $page = (int) ($request->get['page'] ?? 1);
        $posts = $this->postService->getPosts($page);
        $this->render($response, 'posts/index', ['posts' => $posts]);
    }

    public function create(Request $request, Response $response): void
    {
        if ($request->getMethod() === 'POST') {
            $user = $this->authService->getUser();
            $data = json_decode($request->getContent(), true);
            $data['user_id'] = $user->id;

            // Sanitize the Markdown content
            $data['content'] = $this->sanitizeMarkdown($data['content']);

            $result = $this->postService->createPost($data);

            if ($result) {
                $this->jsonResponse($response, ['success' => true, 'message' => 'Post created successfully']);
            } else {
                $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to create post'], 500);
            }
        } else {
            $this->render($response, 'posts/create');
        }
    }

    private function sanitizeMarkdown(string $markdown): string
    {
        // Implement Markdown sanitization here
        // This is a placeholder and should be replaced with actual sanitization logic
        return htmlspecialchars($markdown, ENT_QUOTES, 'UTF-8');
    }

    public function edit(Request $request, Response $response, array $args): void
    {
        $post = $this->postService->getPostById($args['id']);
        if (!$post) {
            $this->jsonResponse($response, ['error' => 'Post not found'], 404);
            return;
        }

        if ($request->getMethod() === 'POST') {
            $data = $request->post;
            $result = $this->postService->updatePost($args['id'], $data);

            if ($result) {
                $this->jsonResponse($response, ['success' => true, 'message' => 'Post updated successfully']);
            } else {
                $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to update post'], 500);
            }
        } else {
            $this->render($response, 'posts/edit', ['post' => $post]);
        }
    }

    public function delete(Request $request, Response $response, array $args): void
    {
        $result = $this->postService->deletePost($args['id']);

        if ($result) {
            $this->jsonResponse($response, ['success' => true, 'message' => 'Post deleted successfully']);
        } else {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to delete post'], 500);
        }
    }

    public function search(Request $request, Response $response): void
    {
        $query = $request->get['q'] ?? '';
        $page = (int) ($request->get['page'] ?? 1);
        $results = $this->postService->searchPosts($query, $page);
        $this->render($response, 'posts/search_results', ['results' => $results, 'query' => $query]);
    }

    public function schedule(Request $request, Response $response, array $args): void
    {
        if ($request->getMethod() === 'POST') {
            $postId = $args['id'];
            $scheduledAt = $request->post['scheduled_at'];
            $result = $this->postService->schedulePost($postId, $scheduledAt);

            if ($result) {
                $this->jsonResponse($response, ['success' => true, 'message' => 'Post scheduled successfully']);
            } else {
                $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to schedule post'], 500);
            }
        } else {
            $post = $this->postService->getPostById($args['id']);
            $this->render($response, 'posts/schedule', ['post' => $post]);
        }
    }
}
