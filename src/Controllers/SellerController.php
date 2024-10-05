<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Interfaces\SellerServiceInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;

class SellerController extends BaseController
{
    public function __construct(
        private readonly SellerServiceInterface $sellerService
    ) {}

    public function manageProduct(Request $request, Response $response, array $args): void
    {
        $productId = (int) $args['id'];
        $product = $this->sellerService->getProductById($productId);

        if (!$product) {
            $this->render($response, 'errors/404', [], 404);
            return;
        }

        $this->render($response, 'seller/manage_product', ['product' => $product]);
    }

    public function updateRelatedProducts(Request $request, Response $response): void
    {
        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $productId = (int) $data['product_id'];
            $relatedProductIds = array_map('intval', $data['related_product_ids']);

            $success = $this->sellerService->updateRelatedProducts($productId, $relatedProductIds);

            $response->header('Content-Type', 'application/json');
            $response->end(json_encode(['success' => $success]));
        } catch (\JsonException $e) {
            $this->handleError($response, 'Invalid JSON data', 400);
        } catch (\Exception $e) {
            $this->handleError($response, 'An error occurred while updating related products', 500);
        }
    }

    private function handleError(Response $response, string $message, int $statusCode): void
    {
        $response->status($statusCode);
        $response->header('Content-Type', 'application/json');
        $response->end(json_encode(['error' => $message]));
    }
}