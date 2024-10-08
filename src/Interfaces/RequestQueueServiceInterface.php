<?php

declare(strict_types=1);

namespace Src\Interfaces;

interface RequestQueueServiceInterface
{
    public function addRequest(string $type, array $params): int;
    public function getRequest(int $id): ?array;
    public function updateRequest(int $id, string $status, string $response = ''): void;
    public function getPendingRequests(): array;
    public function removeRequest(int $id): bool;
    public function clearCompletedRequests(): void;
    public function getTotalRequestCount(): int;
    public function getCompletedRequestCount(): int;
}
