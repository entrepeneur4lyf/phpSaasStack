<?php

declare(strict_types=1);

namespace Src\Controllers;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Src\Core\TwigRenderer;
use Src\Services\PostService;
use Psr\Log\LoggerInterface;

class HomeController extends BaseController
{
    private PostService $postService;
    private LoggerInterface $logger;

    public function __construct(TwigRenderer $twigRenderer, PostService $postService, LoggerInterface $logger)
    {
        parent::__construct($twigRenderer);
        $this->postService = $postService;
        $this->logger = $logger;
    }

    public function index(Request $request, Response $response): void
    {
        $this->render($response, 'home', ['title' => 'Home Page']);
    }

    public function about(Request $request, Response $response): void
    {
        $data = [
            'title' => 'About Us',
            'content' => 'This is the about page content.'
        ];
        $this->render($response, 'home/about', $data);
    }

    public function contact(Request $request, Response $response): void
    {
        if ($request->getMethod() === 'POST') {
            $name = $request->post['name'] ?? '';
            $email = $request->post['email'] ?? '';
            $message = $request->post['message'] ?? '';

            $resultChannel = $this->async(function () use ($name, $email, $message) {
                return $this->processContactForm($name, $email, $message);
            });

            $this->co(function () use ($response, $request, $resultChannel) {
                $result = $resultChannel->pop();
                if ($result['success']) {
                    if ($request->header['HX-Request'] ?? false) {
                        // HTMX request
                        $html = $this->twigRenderer->renderPartial('home/contact_result', $result['data']);
                        $this->htmlResponse($response, $html);
                    } else {
                        // Regular AJAX request
                        $this->jsonResponse($response, $result['data']);
                    }
                } else {
                    $this->jsonResponse($response, ['error' => $result['error']], 500);
                }
            });
        } else {
            $data = [
                'title' => 'Contact Us'
            ];
            $this->render($response, 'home/contact', $data);
        }
    }

    public function getFeaturedPosts(Request $request, Response $response): void
    {
        $featuredPostsChannel = $this->async(function () {
            return $this->postService->getFeaturedPosts();
        });

        $this->co(function () use ($response, $request, $featuredPostsChannel) {
            $result = $featuredPostsChannel->pop();
            if ($result['success']) {
                if ($request->header['HX-Request'] ?? false) {
                    // HTMX request
                    $html = $this->twigRenderer->renderPartial('home/featured_posts', ['featuredPosts' => $result['data']]);
                    $this->htmlResponse($response, $html);
                } else {
                    // AlpineJS data request
                    $this->jsonResponse($response, $result['data']);
                }
            } else {
                $this->jsonResponse($response, ['error' => $result['error']], 500);
            }
        });
    }

    private function processContactForm(string $name, string $email, string $message): array
    {
        $this->logger->info('Processing contact form', ['name' => $name, 'email' => $email]);
        // Here you would typically save the message to a database
        // and perhaps send an email notification

        // Simulate some asynchronous operation
        \Swoole\Coroutine::sleep(1);

        // For now, we'll just return a success message
        return [
            'success' => true,
            'data' => ['message' => 'Your message has been sent.']
        ];
    }

    protected function async(callable $callback)
    {
        $channel = new \Swoole\Coroutine\Channel(1);
        go(function () use ($channel, $callback) {
            $channel->push($callback());
        });
        return $channel;
    }

    protected function co(callable $callback): void
    {
        go($callback);
    }

    protected function htmlResponse(Response $response, string $html): void
    {
        $response->header('Content-Type', 'text/html');
        $response->end($html);
    }
}
