<?php

namespace Src\Controllers;

use Swoole\Http\Request;
use Swoole\Http\Response;

abstract class BaseController
{
    protected function render(Response $response, string $view, array $data = []): void
    {
        ob_start();
        extract($data);
        include __DIR__ . "/../Views/{$view}.php";
        $content = ob_get_clean();
        $response->end($content);
    }

    protected function jsonResponse(Response $response, array $data, int $statusCode = 200): void
    {
        $response->header('Content-Type', 'application/json');
        $response->status($statusCode);
        $response->end(json_encode($data));
    }

    protected function redirect(Response $response, string $url): void
    {
        $response->header('Location', $url);
        $response->status(302);
        $response->end();
    }

    protected function handleFileUpload(Request $request, string $fieldName, string $uploadDir): ?string
    {
        if (!isset($request->files[$fieldName])) {
            return null;
        }

        $file = $request->files[$fieldName];
        $fileName = uniqid() . '_' . $file['name'];
        $filePath = $uploadDir . '/' . $fileName;

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return $fileName;
        }

        return null;
    }

    protected function paginate(array $items, int $page, int $perPage): array
    {
        $totalItems = count($items);
        $totalPages = ceil($totalItems / $perPage);
        $offset = ($page - 1) * $perPage;

        return [
            'data' => array_slice($items, $offset, $perPage),
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_items' => $totalItems,
                'total_pages' => $totalPages,
            ],
        ];
    }

    protected function setFlashMessage(Response $response, string $key, string $message): void
    {
        $response->cookie($key, $message, time() + 5);
    }

    protected function getFlashMessage(Request $request, string $key): ?string
    {
        $message = $request->cookie[$key] ?? null;
        if ($message) {
            // Clear the flash message
            $response = new Response();
            $response->cookie($key, '', time() - 3600);
        }
        return $message;
    }
}