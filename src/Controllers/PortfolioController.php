<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Interfaces\PortfolioServiceInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Src\Exceptions\ImageUploadException;

class PortfolioController extends BaseController
{
    public function __construct(
        private readonly PortfolioServiceInterface $portfolioService
    ) {}

    public function manage(Request $request, Response $response): void
    {
        $user = $request->user;
        $portfolioItems = $this->portfolioModel->getItemsByUserId($user->id);
        
        $this->render($response, 'portfolio/manage', ['portfolioItems' => $portfolioItems]);
    }

    public function addItem(Request $request, Response $response): void
    {
        try {
            $user = $request->user;
            $data = $request->post;

            $itemData = [
                'title' => $data['title'],
                'description' => $data['description'],
                'image_url' => ''
            ];

            if (isset($request->files['image'])) {
                $itemData['image_url'] = $this->portfolioService->handleImageUpload($request->files['image']);
            }

            $result = $this->portfolioService->addItem($user->id, $itemData);

            if ($result) {
                $this->jsonResponse($response, ['success' => true, 'item' => $itemData]);
            } else {
                $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to add portfolio item'], 400);
            }
        } catch (ImageUploadException $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    public function updateItem(Request $request, Response $response): void
    {
        try {
            $data = $request->post;
            $itemId = (int)$data['item_id'];

            $itemData = [
                'title' => $data['title'],
                'description' => $data['description'],
                'image_url' => $data['image_url']
            ];

            if (isset($request->files['image'])) {
                $itemData['image_url'] = $this->portfolioService->handleImageUpload($request->files['image']);
            }

            $result = $this->portfolioService->updateItem($itemId, $itemData);

            if ($result) {
                $this->jsonResponse($response, ['success' => true, 'item' => $itemData]);
            } else {
                $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to update portfolio item'], 400);
            }
        } catch (ImageUploadException $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    public function deleteItem(Request $request, Response $response): void
    {
        try {
            $itemId = (int)$request->post['item_id'];

            $result = $this->portfolioService->deleteItem($itemId);

            if ($result) {
                $this->jsonResponse($response, ['success' => true, 'message' => 'Portfolio item deleted successfully']);
            } else {
                $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to delete portfolio item'], 400);
            }
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    private function handleImageUpload(?array $file): string
    {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return '';
        }

        $uploadDir = __DIR__ . '/../../public/uploads/portfolio/';
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('portfolio_') . '.' . $fileExtension;
        $uploadFile = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
            return '/uploads/portfolio/' . $fileName;
        }

        throw new \RuntimeException('Failed to upload image');
    }

    private function handleError(Response $response, string $message, int $statusCode): void
    {
        $response->status($statusCode);
        $response->header('Content-Type', 'application/json');
        $response->end(json_encode(['success' => false, 'message' => $message]));
    }

    private function render(Response $response, string $view, array $data = []): void
    {
        ob_start();
        extract($data);
        include __DIR__ . "/../Views/{$view}.php";
        $content = ob_get_clean();
        $response->end($content);
    }

    private function jsonResponse(Response $response, array $data, int $statusCode = 200): void
    {
        $response->status($statusCode);
        $response->header('Content-Type', 'application/json');
        $response->end(json_encode($data));
    }
}