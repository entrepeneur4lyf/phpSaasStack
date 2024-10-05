<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Interfaces\UserServiceInterface;
use Src\Interfaces\ProfileServiceInterface;
use Src\Interfaces\PortfolioServiceInterface;
use Src\Interfaces\OfferServiceInterface;
use Src\Interfaces\MessageServiceInterface;
use Src\Interfaces\PostServiceInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;

class ProfileController
{
    public function __construct(
        private readonly UserServiceInterface $userService,
        private readonly ProfileServiceInterface $profileService,
        private readonly PortfolioServiceInterface $portfolioService,
        private readonly OfferServiceInterface $offerService,
        private readonly MessageServiceInterface $messageService,
        private readonly PostServiceInterface $postService
    ) {}

    public function show(Request $request, Response $response, array $params): void
    {
        $userId = (int) ($params['id'] ?? 0);
        $user = $this->userService->getUserById($userId);

        if (!$user) {
            $this->renderError($response, 'User not found', 404);
            return;
        }

        $profile = $this->profileService->getProfileByUserId($userId);
        $portfolio = $this->portfolioService->getPortfolioByUserId($userId);
        $offers = $this->offerService->getOffersByUserId($userId);
        $messages = $this->messageService->getMessagesByUserId($userId);
        $posts = $this->postService->getPostsByUserId($userId);

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

    private function render(Response $response, string $view, array $data = []): void
    {
        // Implement your rendering logic here
        $content = "Rendered view: $view with data: " . json_encode($data);
        $response->end($content);
    }

    private function renderError(Response $response, string $message, int $statusCode): void
    {
        $response->status($statusCode);
        $response->end($message);
    }
}