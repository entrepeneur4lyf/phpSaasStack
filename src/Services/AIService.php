<?php

declare(strict_types=1);

namespace Src\Services\AIService;

use Src\Interfaces\AIServiceInterface;
use Src\Services\OpenAIWrapper;
use Src\Services\RequestQueue;

class AIService implements AIServiceInterface
{
    private OpenAIWrapper $aiWrapper;
    private RequestQueue $requestQueue;

    public function __construct(OpenAIWrapper $aiWrapper, RequestQueue $requestQueue)
    {
        $this->aiWrapper = $aiWrapper;
        $this->requestQueue = $requestQueue;
    }

    public function processRequest(array $request): array
    {
        $type = $request['type'] ?? '';
        $params = $request['params'] ?? [];

        switch ($type) {
            case 'chat_completion':
                return $this->chatCompletion($params['prompt'] ?? '', $params['max_tokens'] ?? 150, $params['temperature'] ?? 0.7);
            case 'image_generation':
                return $this->imageGeneration($params['prompt'] ?? '', $params['number_of_images'] ?? 1, $params['size'] ?? '1024x1024');
            case 'text_moderation':
                return $this->textModeration($params['input'] ?? '');
            default:
                throw new \InvalidArgumentException("Unsupported request type: $type");
        }
    }

    public function chatCompletion(string $prompt, int $maxTokens = 150, float $temperature = 0.7): array
    {
        $id = $this->requestQueue->addRequest('chat_completion', [
            'prompt' => $prompt,
            'max_tokens' => $maxTokens,
            'temperature' => $temperature
        ]);

        // Process the request immediately (in a real-world scenario, this might be handled by a separate worker process)
        $this->processQueue();

        $result = $this->requestQueue->getRequest($id);
        return [
            'id' => $id,
            'status' => $result['status'],
            'response' => $result['response'] ?? null
        ];
    }

    public function imageGeneration(string $prompt, int $numberOfImages = 1, string $size = '1024x1024'): array
    {
        // Implement image generation logic here
        // For now, we'll return a placeholder response
        return [
            'status' => 'not_implemented',
            'message' => 'Image generation is not yet implemented'
        ];
    }

    public function textModeration(string $input): array
    {
        // Implement text moderation logic here
        // For now, we'll return a placeholder response
        return [
            'status' => 'not_implemented',
            'message' => 'Text moderation is not yet implemented'
        ];
    }

    public function getModelList(): array
    {
        // Implement logic to fetch available models
        // For now, we'll return a placeholder response
        return [
            'models' => ['gpt-3.5-turbo', 'gpt-4']
        ];
    }

    public function getUsage(): array
    {
        // Implement logic to fetch usage statistics
        // For now, we'll return a placeholder response
        return [
            'total_requests' => $this->requestQueue->getTotalRequestCount(),
            'completed_requests' => $this->requestQueue->getCompletedRequestCount()
        ];
    }

    private function processQueue(): void
    {
        $pendingRequests = $this->requestQueue->getPendingRequests();
        foreach ($pendingRequests as $request) {
            try {
                $aiResponse = $this->aiWrapper->chatCompletion($request['params']['prompt'], $request['params']['max_tokens'], $request['params']['temperature']);
                $this->requestQueue->updateRequest($request['id'], 'completed', $aiResponse);
            } catch (\Exception $e) {
                $this->requestQueue->updateRequest($request['id'], 'error', $e->getMessage());
            }
        }
    }
}