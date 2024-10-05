<?php

namespace Src\Controllers;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Src\Models\Post;
use Src\Models\Product;
use Src\Models\User;

class HomeController extends BaseController
{
    private $postModel;
    private $productModel;
    private $userModel;

    public function __construct()
    {
        $this->postModel = new Post();
        $this->productModel = new Product();
        $this->userModel = new User();
    }

    public function index(Request $request, Response $response): void
    {
        $featuredPosts = $this->postModel->getFeaturedPosts(5);
        $latestProducts = $this->productModel->getLatestProducts(6);
        $topSellers = $this->userModel->getTopSellers(5);

        $data = [
            'featuredPosts' => $featuredPosts,
            'latestProducts' => $latestProducts,
            'topSellers' => $topSellers,
        ];

        $this->render($response, 'home/index', $data);
    }

    public function about(Request $request, Response $response): void
    {
        $this->render($response, 'home/about');
    }

    public function contact(Request $request, Response $response): void
    {
        if ($request->getMethod() === 'POST') {
            // Handle contact form submission
            $name = $request->post['name'] ?? '';
            $email = $request->post['email'] ?? '';
            $message = $request->post['message'] ?? '';

            // TODO: Implement contact form processing (e.g., send email, save to database)

            $this->jsonResponse($response, ['success' => true, 'message' => 'Your message has been sent.']);
        } else {
            $this->render($response, 'home/contact');
        }
    }

    public function terms(Request $request, Response $response): void
    {
        $this->render($response, 'home/terms');
    }

    public function privacy(Request $request, Response $response): void
    {
        $this->render($response, 'home/privacy');
    }
}