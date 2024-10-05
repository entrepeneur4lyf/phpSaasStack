<?php

declare(strict_types=1);

namespace Src\Services;

use Src\Interfaces\RequestQueueServiceInterface;
use Swoole\Table;

class RequestQueueService implements RequestQueueServiceInterface
{
    private Table $table;

    public function __construct()
    {
        $this->table = new Table(1024);
        $this->table->column('id', Table::TYPE_INT);
        $this->table->column('type', Table::TYPE_STRING, 32);
        $this->table->column('params', Table::TYPE_STRING, 1024);
        $this->table->column('status', Table::TYPE_STRING, 20);
        $this->table->column('response', Table::TYPE_STRING, 4096);
        $this->table->create();
    }

    public function addRequest(string $type, array $params): int
    {
        $id = $this->table->count() + 1;
        $this->table->set($id, [
            'id' => $id,
            'type' => $type,
            'params' => json_encode($params),
            'status' => 'pending',
            'response' => '',
        ]);
        return $id;
    }

    public function getRequest(int $id): ?array
    {
        $request = $this->table->get($id);
        if ($request) {
            $request['params'] = json_decode($request['params'], true);
        }
        return $request ?: null;
    }

    public function updateRequest(int $id, string $status, string $response = ''): void
    {
        $this->table->set($id, [
            'status' => $status,
            'response' => $response,
        ]);
    }

    public function getPendingRequests(): array
    {
        $pending = [];
        foreach ($this->table as $id => $row) {
            if ($row['status'] === 'pending') {
                $row['params'] = json_decode($row['params'], true);
                $pending[] = $row;
            }
        }
        return $pending;
    }

    public function removeRequest(int $id): bool
    {
        return $this->table->del($id);
    }

    public function clearCompletedRequests(): void
    {
        foreach ($this->table as $id => $row) {
            if ($row['status'] === 'completed') {
                $this->table->del($id);
            }
        }
    }

    public function getTotalRequestCount(): int
    {
        return $this->table->count();
    }

    public function getCompletedRequestCount(): int
    {
        $count = 0;
        foreach ($this->table as $row) {
            if ($row['status'] === 'completed') {
                $count++;
            }
        }
        return $count;
    }
}