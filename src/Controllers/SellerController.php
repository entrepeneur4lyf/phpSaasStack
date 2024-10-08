<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Core\TwigRenderer;
use Src\Interfaces\SellerServiceInterface;
use Src\Services\AuthService;
use Swoole\Http\Request;
use Swoole\Http\Response;

class SellerController extends BaseController
{
    private SellerServiceInterface $sellerService;
    private AuthService $authService;

    public function __construct(
        TwigRenderer $twigRenderer,
        SellerServiceInterface $sellerService,
        AuthService $authService
    ) {
        parent::__construct($twigRenderer);
        $this->sellerService = $sellerService;
        $this->authService = $authService;
    }

    public function manageProduct(Request $request, Response $response, array $args): void
    {
        $user = $this->authService->getUser();
        if (!$user->isSeller()) {
            $this->jsonResponse($response, ['error' => 'Unauthorized'], 403);
            return;
        }

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
        $user = $this->authService->getUser();
        if (!$user->isSeller()) {
            $this->jsonResponse($response, ['error' => 'Unauthorized'], 403);
            return;
        }

        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $productId = (int) $data['product_id'];
            $relatedProductIds = array_map('intval', $data['related_product_ids']);

            $success = $this->sellerService->updateRelatedProducts($productId, $relatedProductIds);

            $this->jsonResponse($response, ['success' => $success]);
        } catch (\JsonException $e) {
            $this->jsonResponse($response, ['error' => 'Invalid JSON data'], 400);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['error' => 'An error occurred while updating related products'], 500);
        }
    }
}
