<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Interfaces\AIServiceInterface;
use Src\Interfaces\AuthServiceInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;

class AIController
{
    public function __construct(
        private AIServiceInterface $aiService,
        private AuthServiceInterface $authService
    ) {}

    public function showInterface(Request $request, Response $response): void
    {
        if (!$this->authService->isAuthenticated()) {
            $response->status(401);
            $response->end('Unauthorized');
            return;
        }

        $content = file_get_contents(__DIR__ . '/../Views/ai/interface.php');
        $response->header('Content-Type', 'text/html');
        $response->end($content);
    }

    public function chatCompletion(Request $request, Response $response): void
    {
        if (!$this->authService->isAuthenticated()) {
            $response->status(401);
            $response->end(json_encode(['error' => 'Unauthorized']));
            return;
        }

        $result = $this->aiService->processRequest([
            'type' => 'chat_completion',
            'params' => [
                'prompt' => $request->post['prompt'],
                'max_tokens' => $request->post['max_tokens'] ?? 150,
                'temperature' => $request->post['temperature'] ?? 0.7
            ]
        ]);

        $this->jsonResponse($response, $result);
    }
}