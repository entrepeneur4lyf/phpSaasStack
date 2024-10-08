<?php

declare(strict_types=1);

namespace Src\Interfaces;

interface CommentServiceInterface
{
    public function getThreadedComments(int $postId): array;
    public function vote(int $id, string $type): array;
    public function create(array $data): bool;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}
