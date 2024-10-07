<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Core\TwigRenderer;
use Src\Services\AIService;
use Src\Services\AuthService;
use Swoole\Http\Request;
use Swoole\Http\Response;

class AIController extends BaseController
{
    protected AIService $aiService;
    protected AuthService $authService;

    public function __construct(TwigRenderer $twigRenderer, AIService $aiService, AuthService $authService)
    {
        parent::__construct($twigRenderer);
        $this->aiService = $aiService;
        $this->authService = $authService;
    }

    public function index(Request $request, Response $response): void
    {
        $user = $this->authService->getUser();
        $this->render($response, 'ai/interface', ['user' => $user]);
    }

    public function generateContent(Request $request, Response $response): void
    {
        $user = $this->authService->getUser();
        $prompt = $request->post['prompt'] ?? '';
        
        $result = $this->aiService->generateContent($user->id, $prompt);

        $this->jsonResponse($response, $result);
    }

    public function getHistory(Request $request, Response $response): void
    {
        $user = $this->authService->getUser();
        $history = $this->aiService->getGenerationHistory($user->id);

        $this->jsonResponse($response, $history);
    }

    public function analyzeImage(Request $request, Response $response): void
    {
        $user = $this->authService->getUser();
        $image = $request->files['image'] ?? null;

        if ($image && $image['error'] === UPLOAD_ERR_OK) {
            $result = $this->aiService->analyzeImage($user->id, $image);
            $this->jsonResponse($response, $result);
        } else {
            $this->jsonResponse($response, ['error' => 'Invalid image upload'], 400);
        }
    }

    public function chatbot(Request $request, Response $response): void
    {
        $user = $this->authService->getUser();
        $message = $request->post['message'] ?? '';

        $response = $this->aiService->chatbotResponse($user->id, $message);

        $this->jsonResponse($response, $response);
    }
}