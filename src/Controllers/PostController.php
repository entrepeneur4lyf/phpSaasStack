<?php

namespace App\Controllers;

use Twig\Environment;
use App\Services\PostService;
use App\Services\AuthService;

class PostController extends BaseController
{
    protected $twig;
    protected $postService;
    protected $authService;

    public function __construct(Environment $twig, PostService $postService, AuthService $authService)
    {
        $this->twig = $twig;
        $this->postService = $postService;
        $this->authService = $authService;
    }

    public function index()
    {
        $posts = $this->postService->getAllPosts();
        return $this->twig->render('post/index.twig', ['posts' => $posts]);
    }

    public function create()
    {
        return $this->twig->render('post/create.twig');
    }

    public function store()
    {
        $user = $this->authService->getUser();
        $data = $this->request->getPost();
        $data['user_id'] = $user->id;

        $result = $this->postService->createPost($data);

        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => 'Post created successfully']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Failed to create post']);
        }
    }

    public function view($id)
    {
        $post = $this->postService->getPostById($id);
        if (!$post) {
            return $this->response->setStatusCode(404)->setBody('Post not found');
        }
        return $this->twig->render('post/view.twig', ['post' => $post]);
    }

    public function edit($id)
    {
        $user = $this->authService->getUser();
        $post = $this->postService->getPostById($id);

        if (!$post) {
            return $this->response->setStatusCode(404)->setBody('Post not found');
        }

        if ($post->user_id != $user->id && !$user->isAdmin()) {
            return $this->response->setStatusCode(403)->setBody('Unauthorized');
        }

        return $this->twig->render('post/edit.twig', ['post' => $post]);
    }

    public function update($id)
    {
        $user = $this->authService->getUser();
        $post = $this->postService->getPostById($id);

        if (!$post) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Post not found']);
        }

        if ($post->user_id != $user->id && !$user->isAdmin()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $data = $this->request->getPost();
        $result = $this->postService->updatePost($id, $data);

        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => 'Post updated successfully']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Failed to update post']);
        }
    }

    public function delete($id)
    {
        $user = $this->authService->getUser();
        $post = $this->postService->getPostById($id);

        if (!$post) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Post not found']);
        }

        if ($post->user_id != $user->id && !$user->isAdmin()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $result = $this->postService->deletePost($id);

        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => 'Post deleted successfully']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Failed to delete post']);
        }
    }
}