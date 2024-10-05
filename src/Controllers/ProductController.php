<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Interfaces\ProductServiceInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Src\Exceptions\ValidationException;

class ProductController extends BaseController
{
    public function __construct(
        private readonly ProductServiceInterface $productService
    ) {}

    public function listProducts(Request $request, Response $response): void
    {
        $page = (int) ($request->get['page'] ?? 1);
        $limit = (int) ($request->get['limit'] ?? 20);

        $products = $this->productService->getProducts($page, $limit);
        $this->render($response, 'products/list', ['products' => $products]);
    }

    public function showProduct(Request $request, Response $response, array $args): void
    {
        $productId = (int) $args['id'];
        $product = $this->productService->getProductById($productId);

        if (!$product) {
            $this->render($response, 'errors/404', [], 404);
            return;
        }

        $this->render($response, 'products/show', ['product' => $product]);
    }

    public function createProduct(Request $request, Response $response): void
    {
        try {
            $productData = $request->post;
            $productId = $this->productService->createProduct($productData);
            $this->jsonResponse($response, ['success' => true, 'product_id' => $productId]);
        } catch (ValidationException $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred while creating the product'], 500);
        }
    }

    public function updateProduct(Request $request, Response $response, array $args): void
    {
        try {
            $productId = (int) $args['id'];
            $productData = $request->post;
            $success = $this->productService->updateProduct($productId, $productData);
            $this->jsonResponse($response, ['success' => $success]);
        } catch (ValidationException $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred while updating the product'], 500);
        }
    }

    public function deleteProduct(Request $request, Response $response, array $args): void
    {
        $productId = (int) $args['id'];
        $success = $this->productService->deleteProduct($productId);
        $this->jsonResponse($response, ['success' => $success]);
    }

    public function searchProducts(Request $request, Response $response): void
    {
        $filters = $request->get;
        $products = $this->productService->searchProducts($filters);
        $this->jsonResponse($response, ['success' => true, 'products' => $products]);
    }

    public function getProductAnalytics(Request $request, Response $response, array $args): void
    {
        $productId = (int) $args['id'];
        $analytics = $this->productService->getProductAnalytics($productId);
        $this->jsonResponse($response, ['success' => true, 'analytics' => $analytics]);
    }

    public function addProductAsset(Request $request, Response $response, array $args): void
    {
        try {
            $productId = (int) $args['id'];
            $fileData = $request->files['asset'];
            $userId = $request->user->id; // Assuming user is set by middleware
            $assetId = $this->productService->addProductAsset($productId, $fileData, $userId);
            $this->jsonResponse($response, ['success' => true, 'asset_id' => $assetId]);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to add asset'], 500);
        }
    }

    public function removeProductAsset(Request $request, Response $response, array $args): void
    {
        $productId = (int) $args['product_id'];
        $assetId = (int) $args['asset_id'];
        $success = $this->productService->removeProductAsset($productId, $assetId);
        $this->jsonResponse($response, ['success' => $success]);
    }

    public function getProductAssets(Request $request, Response $response, array $args): void
    {
        $productId = (int) $args['id'];
        $assets = $this->productService->getProductAssets($productId);
        $this->jsonResponse($response, ['success' => true, 'assets' => $assets]);
    }

    public function getProductLicenseInfo(Request $request, Response $response, array $args): void
    {
        $productId = (int) $args['id'];
        $licenseInfo = $this->productService->getProductLicenseInfo($productId);
        $this->jsonResponse($response, ['success' => true, 'license_info' => $licenseInfo]);
    }
}