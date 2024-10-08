<?php

declare(strict_types=1);

namespace Src\Utils;

use Swoole\Table;

class RateLimiter
{
    private Table $table;
    private int $limit;
    private int $window;

    public function __construct(int $limit, int $window)
    {
        $this->limit = $limit;
        $this->window = $window;
        $this->table = new Table(1024);
        $this->table->column('count', Table::TYPE_INT);
        $this->table->column('reset', Table::TYPE_INT);
        $this->table->create();
    }

    public function attempt(string $key): bool
    {
        $now = time();
        $data = $this->table->get($key);

        if (!$data) {
            $this->table->set($key, ['count' => 1, 'reset' => $now + $this->window]);
            return true;
        }

        if ($now > $data['reset']) {
            $this->table->set($key, ['count' => 1, 'reset' => $now + $this->window]);
            return true;
        }

        if ($data['count'] < $this->limit) {
            $this->table->incr($key, 'count');
            return true;
        }

        return false;
    }
}
