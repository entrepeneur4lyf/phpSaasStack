<?php

declare(strict_types=1);

namespace Src\Core;

use Swoole\Http\Response;
use Swoole\Coroutine;
use Psr\Log\LoggerInterface;

abstract class Controller
{
    protected TwigRenderer $renderer;
    protected LoggerInterface $logger;

    public function __construct(TwigRenderer $renderer, LoggerInterface $logger)
    {
        $this->renderer = $renderer;
        $this->logger = $logger;
    }

    protected function render(Response $response, string $view, array $data = []): void
    {
        $this->logger->info('Rendering view', ['view' => $view, 'data' => $data]);
        $content = $this->renderer->render($view, $data);
        $response->end($content);
    }

    protected function jsonResponse(Response $response, array $data, int $statusCode = 200): void
    {
        $this->logger->info('Sending JSON response', ['data' => $data, 'statusCode' => $statusCode]);
        $response->header('Content-Type', 'application/json');
        $response->status($statusCode);
        $response->end(json_encode($data));
    }

    protected function htmlResponse(Response $response, string $html, int $statusCode = 200): void
    {
        $this->logger->info('Sending HTML response', ['statusCode' => $statusCode]);
        $response->header('Content-Type', 'text/html');
        $response->status($statusCode);
        $response->end($html);
    }

    protected function redirect(Response $response, string $url, int $statusCode = 302): void
    {
        $this->logger->info('Redirecting', ['url' => $url, 'statusCode' => $statusCode]);
        $response->header('Location', $url);
        $response->status($statusCode);
        $response->end();
    }

    protected function defer(callable $callback): void
    {
        $this->logger->debug('Deferring task');
        Coroutine::defer($callback);
    }

    protected function co(callable $callback): void
    {
        $this->logger->debug('Creating coroutine');
        Coroutine::create($callback);
    }

    protected function async(callable $callback): \Swoole\Coroutine\Channel
    {
        $this->logger->debug('Starting async operation');
        $channel = new \Swoole\Coroutine\Channel(1);
        $this->co(function () use ($callback, $channel) {
            try {
                $result = $callback();
                $channel->push(['success' => true, 'data' => $result]);
                $this->logger->debug('Async operation completed successfully');
            } catch (\Throwable $e) {
                $channel->push(['success' => false, 'error' => $e->getMessage()]);
                $this->logger->error('Async operation failed', ['error' => $e->getMessage()]);
            }
        });
        return $channel;
    }
}