<?php

declare(strict_types=1);

namespace App\Controllers;

use Twig\Environment;
use App\Services\AIService;
use App\Services\AuthService;

class AIController extends BaseController
{
    protected $twig;
    protected $aiService;
    protected $authService;

    public function __construct(Environment $twig, AIService $aiService, AuthService $authService)
    {
        $this->twig = $twig;
        $this->aiService = $aiService;
        $this->authService = $authService;
    }

    public function index()
    {
        $user = $this->authService->getUser();
        return $this->twig->render('ai/interface.twig', ['user' => $user]);
    }

    public function generateContent()
    {
        $user = $this->authService->getUser();
        $prompt = $this->request->getPost('prompt');
        
        $result = $this->aiService->generateContent($user->id, $prompt);

        return $this->response->setJSON($result);
    }

    public function getHistory()
    {
        $user = $this->authService->getUser();
        $history = $this->aiService->getGenerationHistory($user->id);

        return $this->response->setJSON($history);
    }

    public function analyzeImage()
    {
        $user = $this->authService->getUser();
        $image = $this->request->getFile('image');

        if ($image->isValid() && !$image->hasMoved()) {
            $result = $this->aiService->analyzeImage($user->id, $image);
            return $this->response->setJSON($result);
        }

        return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid image upload']);
    }

    public function chatbot()
    {
        $user = $this->authService->getUser();
        $message = $this->request->getPost('message');

        $response = $this->aiService->chatbotResponse($user->id, $message);

        return $this->response->setJSON($response);
    }
}