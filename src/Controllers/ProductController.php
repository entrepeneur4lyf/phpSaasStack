<?php

namespace App\Controllers;

use Twig\Environment;
use App\Services\ProductService;
use App\Services\AuthService;

class ProductController extends BaseController
{
    protected $twig;
    protected $productService;
    protected $authService;

    public function __construct(Environment $twig, ProductService $productService, AuthService $authService)
    {
        $this->twig = $twig;
        $this->productService = $productService;
        $this->authService = $authService;
    }

    public function index()
    {
        $products = $this->productService->getAllProducts();
        return $this->twig->render('product/index.twig', ['products' => $products]);
    }

    public function show($id)
    {
        $product = $this->productService->getProductById($id);
        if (!$product) {
            return $this->response->setStatusCode(404)->setBody('Product not found');
        }
        return $this->twig->render('product/show.twig', ['product' => $product]);
    }

    public function create()
    {
        $user = $this->authService->getUser();
        if (!$user->isSeller()) {
            return $this->response->setStatusCode(403)->setBody('Unauthorized');
        }
        return $this->twig->render('product/create.twig');
    }

    public function store()
    {
        $user = $this->authService->getUser();
        if (!$user->isSeller()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $data = $this->request->getPost();
        $data['seller_id'] = $user->id;

        $result = $this->productService->createProduct($data);

        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => 'Product created successfully']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Failed to create product']);
        }
    }

    public function edit($id)
    {
        $user = $this->authService->getUser();
        $product = $this->productService->getProductById($id);

        if (!$product) {
            return $this->response->setStatusCode(404)->setBody('Product not found');
        }

        if ($product->seller_id !== $user->id && !$user->isAdmin()) {
            return $this->response->setStatusCode(403)->setBody('Unauthorized');
        }

        return $this->twig->render('product/edit.twig', ['product' => $product]);
    }

    public function update($id)
    {
        $user = $this->authService->getUser();
        $product = $this->productService->getProductById($id);

        if (!$product) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Product not found']);
        }

        if ($product->seller_id !== $user->id && !$user->isAdmin()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $data = $this->request->getPost();
        $result = $this->productService->updateProduct($id, $data);

        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => 'Product updated successfully']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Failed to update product']);
        }
    }

    public function delete($id)
    {
        $user = $this->authService->getUser();
        $product = $this->productService->getProductById($id);

        if (!$product) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Product not found']);
        }

        if ($product->seller_id !== $user->id && !$user->isAdmin()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $result = $this->productService->deleteProduct($id);

        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => 'Product deleted successfully']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Failed to delete product']);
        }
    }
}