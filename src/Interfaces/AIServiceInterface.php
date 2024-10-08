<?php

declare(strict_types=1);

namespace Src\Interfaces;

interface AIServiceInterface
{
    public function processRequest(array $request): array;
    public function chatCompletion(string $prompt, int $maxTokens = 150, float $temperature = 0.7): array;
    public function imageGeneration(string $prompt, int $numberOfImages = 1, string $size = '1024x1024'): array;
    public function textModeration(string $input): array;
    public function getModelList(): array;
    public function getUsage(): array;
}
