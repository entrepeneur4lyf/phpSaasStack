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
        $posts = $this->postService->getAllPosts();
        $this->render($response, 'post/index', ['posts' => $posts]);
    }

    public function create(Request $request, Response $response): void
    {
        $this->render($response, 'post/create');
    }

    public function store(Request $request, Response $response): void
    {
        $user = $this->authService->getUser();
        $data = $request->post;
        $data['user_id'] = $user->id;

        $result = $this->postService->createPost($data);

        if ($result) {
            $this->jsonResponse($response, ['success' => true, 'message' => 'Post created successfully']);
        } else {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to create post'], 500);
        }
    }

    public function view(Request $request, Response $response, array $args): void
    {
        $post = $this->postService->getPostById($args['id']);
        if (!$post) {
            $this->jsonResponse($response, ['error' => 'Post not found'], 404);
            return;
        }
        $this->render($response, 'post/view', ['post' => $post]);
    }

    public function edit(Request $request, Response $response, array $args): void
    {
        $user = $this->authService->getUser();
        $post = $this->postService->getPostById($args['id']);

        if (!$post) {
            $this->jsonResponse($response, ['error' => 'Post not found'], 404);
            return;
        }

        if ($post->user_id != $user->id && !$user->isAdmin()) {
            $this->jsonResponse($response, ['error' => 'Unauthorized'], 403);
            return;
        }

        $this->render($response, 'post/edit', ['post' => $post]);
    }

    public function update(Request $request, Response $response, array $args): void
    {
        $user = $this->authService->getUser();
        $post = $this->postService->getPostById($args['id']);

        if (!$post) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Post not found'], 404);
            return;
        }

        if ($post->user_id != $user->id && !$user->isAdmin()) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Unauthorized'], 403);
            return;
        }

        $data = $request->post;
        $result = $this->postService->updatePost($args['id'], $data);

        if ($result) {
            $this->jsonResponse($response, ['success' => true, 'message' => 'Post updated successfully']);
        } else {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to update post'], 500);
        }
    }

    public function delete(Request $request, Response $response, array $args): void
    {
        $user = $this->authService->getUser();
        $post = $this->postService->getPostById($args['id']);

        if (!$post) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Post not found'], 404);
            return;
        }

        if ($post->user_id != $user->id && !$user->isAdmin()) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Unauthorized'], 403);
            return;
        }

        $result = $this->postService->deletePost($args['id']);

        if ($result) {
            $this->jsonResponse($response, ['success' => true, 'message' => 'Post deleted successfully']);
        } else {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to delete post'], 500);
        }
    }
}