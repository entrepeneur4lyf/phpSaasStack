<?php

declare(strict_types=1);

namespace App\Controllers;

use Twig\Environment;
use App\Services\UserService;
use App\Services\ProfileService;
use App\Services\PortfolioService;
use App\Services\OfferService;
use App\Services\MessageService;
use App\Services\PostService;
use App\Services\AuthService;

class ProfileController extends BaseController
{
    protected $twig;
    protected $userService;
    protected $profileService;
    protected $portfolioService;
    protected $offerService;
    protected $messageService;
    protected $postService;
    protected $authService;

    public function __construct(
        Environment $twig,
        UserService $userService,
        ProfileService $profileService,
        PortfolioService $portfolioService,
        OfferService $offerService,
        MessageService $messageService,
        PostService $postService,
        AuthService $authService
    ) {
        $this->twig = $twig;
        $this->userService = $userService;
        $this->profileService = $profileService;
        $this->portfolioService = $portfolioService;
        $this->offerService = $offerService;
        $this->messageService = $messageService;
        $this->postService = $postService;
        $this->authService = $authService;
    }

    public function show($id)
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return $this->response->setStatusCode(404)->setBody('User not found');
        }

        $profile = $this->profileService->getProfileByUserId($id);
        $portfolio = $this->portfolioService->getPortfolioByUserId($id);
        $offers = $this->offerService->getOffersByUserId($id);
        $messages = $this->messageService->getMessagesByUserId($id);
        $posts = $this->postService->getPostsByUserId($id);

        $viewData = [
            'user' => $user,
            'profile' => $profile,
            'portfolio' => $portfolio,
            'offers' => $offers,
            'messages' => $messages,
            'posts' => $posts,
        ];

        return $this->twig->render('profile/show.twig', $viewData);
    }

    public function edit()
    {
        $user = $this->authService->getUser();
        $profile = $this->profileService->getProfileByUserId($user->id);

        return $this->twig->render('profile/edit.twig', [
            'user' => $user,
            'profile' => $profile
        ]);
    }

    public function update()
    {
        $user = $this->authService->getUser();
        $data = $this->request->getPost();

        $result = $this->profileService->updateProfile($user->id, $data);

        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => 'Profile updated successfully']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Failed to update profile']);
        }
    }
}