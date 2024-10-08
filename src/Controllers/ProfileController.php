<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Core\TwigRenderer;
use Src\Services\UserService;
use Src\Services\ProfileService;
use Src\Services\PortfolioService;
use Src\Services\OfferService;
use Src\Services\MessageService;
use Src\Services\PostService;
use Src\Services\AuthService;
use Swoole\Http\Request;
use Swoole\Http\Response;

class ProfileController extends BaseController
{
    protected UserService $userService;
    protected ProfileService $profileService;
    protected PortfolioService $portfolioService;
    protected OfferService $offerService;
    protected MessageService $messageService;
    protected PostService $postService;
    protected AuthService $authService;

    public function __construct(
        TwigRenderer $twigRenderer,
        UserService $userService,
        ProfileService $profileService,
        PortfolioService $portfolioService,
        OfferService $offerService,
        MessageService $messageService,
        PostService $postService,
        AuthService $authService
    ) {
        parent::__construct($twigRenderer);
        $this->userService = $userService;
        $this->profileService = $profileService;
        $this->portfolioService = $portfolioService;
        $this->offerService = $offerService;
        $this->messageService = $messageService;
        $this->postService = $postService;
        $this->authService = $authService;
    }

    public function show(Request $request, Response $response, array $args): void
    {
        $user = $this->userService->getUserById($args['id']);

        if (!$user) {
            $this->jsonResponse($response, ['error' => 'User not found'], 404);
            return;
        }

        $profile = $this->profileService->getProfileByUserId($args['id']);
        $portfolio = $this->portfolioService->getPortfolioByUserId($args['id']);
        $offers = $this->offerService->getOffersByUserId($args['id']);
        $messages = $this->messageService->getMessagesByUserId($args['id']);
        $posts = $this->postService->getPostsByUserId($args['id']);

        $viewData = [
            'user' => $user,
            'profile' => $profile,
            'portfolio' => $portfolio,
            'offers' => $offers,
            'messages' => $messages,
            'posts' => $posts,
        ];

        $this->render($response, 'profile/show', $viewData);
    }

    public function edit(Request $request, Response $response): void
    {
        $user = $this->authService->getUser();
        $profile = $this->profileService->getProfileByUserId($user->id);

        $this->render($response, 'profile/edit', [
            'user' => $user,
            'profile' => $profile
        ]);
    }

    public function update(Request $request, Response $response): void
    {
        $user = $this->authService->getUser();
        $data = $request->post;

        $result = $this->profileService->updateProfile($user->id, $data);

        if ($result) {
            $this->jsonResponse($response, ['success' => true, 'message' => 'Profile updated successfully']);
        } else {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to update profile'], 500);
        }
    }
}
