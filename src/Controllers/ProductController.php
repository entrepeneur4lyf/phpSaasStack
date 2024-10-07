<?php

namespace Src\Controllers;

use Src\Core\TwigRenderer;
use Src\Services\ProductService;
use Src\Services\AuthService;
use Swoole\Http\Request;
use Swoole\Http\Response;

class ProductController extends BaseController
{
    protected ProductService $productService;
    protected AuthService $authService;

    public function __construct(TwigRenderer $twigRenderer, ProductService $productService, AuthService $authService)
    {
        parent::__construct($twigRenderer);
        $this->productService = $productService;
        $this->authService = $authService;
    }

    public function index(Request $request, Response $response): void
    {
        $products = $this->productService->getAllProducts();
        $this->render($response, 'product/index', ['products' => $products]);
    }

    public function show(Request $request, Response $response, array $args): void
    {
        $product = $this->productService->getProductById($args['id']);
        if (!$product) {
            $this->jsonResponse($response, ['error' => 'Product not found'], 404);
            return;
        }
        $this->render($response, 'product/show', ['product' => $product]);
    }

    public function create(Request $request, Response $response): void
    {
        $user = $this->authService->getUser();
        if (!$user->isSeller()) {
            $this->jsonResponse($response, ['error' => 'Unauthorized'], 403);
            return;
        }
        $this->render($response, 'product/create');
    }

    public function store(Request $request, Response $response): void
    {
        $user = $this->authService->getUser();
        if (!$user->isSeller()) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Unauthorized'], 403);
            return;
        }

        $data = $request->post;
        $data['seller_id'] = $user->id;

        $result = $this->productService->createProduct($data);

        if ($result) {
            $this->jsonResponse($response, ['success' => true, 'message' => 'Product created successfully']);
        } else {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to create product'], 500);
        }
    }

    public function edit(Request $request, Response $response, array $args): void
    {
        $user = $this->authService->getUser();
        $product = $this->productService->getProductById($args['id']);

        if (!$product) {
            $this->jsonResponse($response, ['error' => 'Product not found'], 404);
            return;
        }

        if ($product->seller_id !== $user->id && !$user->isAdmin()) {
            $this->jsonResponse($response, ['error' => 'Unauthorized'], 403);
            return;
        }

        $this->render($response, 'product/edit', ['product' => $product]);
    }

    public function update(Request $request, Response $response, array $args): void
    {
        $user = $this->authService->getUser();
        $product = $this->productService->getProductById($args['id']);

        if (!$product) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Product not found'], 404);
            return;
        }

        if ($product->seller_id !== $user->id && !$user->isAdmin()) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Unauthorized'], 403);
            return;
        }

        $data = $request->post;
        $result = $this->productService->updateProduct($args['id'], $data);

        if ($result) {
            $this->jsonResponse($response, ['success' => true, 'message' => 'Product updated successfully']);
        } else {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to update product'], 500);
        }
    }

    public function delete(Request $request, Response $response, array $args): void
    {
        $user = $this->authService->getUser();
        $product = $this->productService->getProductById($args['id']);

        if (!$product) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Product not found'], 404);
            return;
        }

        if ($product->seller_id !== $user->id && !$user->isAdmin()) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Unauthorized'], 403);
            return;
        }

        $result = $this->productService->deleteProduct($args['id']);

        if ($result) {
            $this->jsonResponse($response, ['success' => true, 'message' => 'Product deleted successfully']);
        } else {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to delete product'], 500);
        }
    }
}