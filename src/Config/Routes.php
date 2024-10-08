<?php

declare(strict_types=1);

use Src\Core\RouteCollection;
use Src\Controllers\AIController;
use Src\Controllers\UserController;
use Src\Middleware\ValidationSanitizationMiddleware;

return function (RouteCollection $routes) {
    // Home routes
    $routes->get('/', ['Src\Controllers\HomeController', 'index']);
    $routes->get('/about', ['Src\Controllers\HomeController', 'about']);
    $routes->get('/contact', ['Src\Controllers\HomeController', 'contact']);
    $routes->post('/contact', ['Src\Controllers\HomeController', 'contact']);
    $routes->get('/terms', ['Src\Controllers\HomeController', 'terms']);
    $routes->get('/privacy', ['Src\Controllers\HomeController', 'privacy']);

    // Auth routes
    $routes->get('/register', ['Src\Controllers\AuthController', 'showRegistrationForm']);
    $routes->post('/register', [
        'handler' => [UserController::class, 'register'],
        'middleware' => [
            new ValidationSanitizationMiddleware([
                'email' => 'required|email|max:255',
                'password' => 'required|min:8',
                'name' => 'required|max:255'
            ])
        ]
    ]);
    $routes->get('/login', ['Src\Controllers\AuthController', 'showLoginForm']);
    $routes->post('/login', [
        'handler' => [UserController::class, 'login'],
        'middleware' => [
            new ValidationSanitizationMiddleware([
                'email' => 'required|email',
                'password' => 'required'
            ])
        ]
    ]);
    $routes->post('/logout', ['Src\Controllers\UserController', 'logout']);
    $routes->get('/verify-email', ['Src\Controllers\UserController', 'verifyEmail']);
    $routes->post('/reset-password-request', ['Src\Controllers\UserController', 'resetPasswordRequest']);
    $routes->post('/reset-password', ['Src\Controllers\UserController', 'resetPassword']);
    $routes->post('/change-password', ['Src\Controllers\UserController', 'changePassword']);

    // User management routes
    $routes->get('/users', ['Src\Controllers\UserController', 'listUsers']);
    $routes->put('/users/{id:\d+}', ['Src\Controllers\UserController', 'updateUser']);
    $routes->delete('/users/{id:\d+}', ['Src\Controllers\UserController', 'deleteUser']);

    // Product routes
    $routes->get('/products', ['Src\Controllers\ProductController', 'index']);
    $routes->get('/product/{id:\d+}', ['Src\Controllers\ProductController', 'detail']);
    $routes->get('/add-product', ['Src\Controllers\ProductController', 'showAddProductForm']);
    $routes->post('/add-product', [
        'handler' => ['Src\Controllers\ProductController', 'addProduct'],
        'middleware' => [
            new ValidationSanitizationMiddleware([
                'name' => 'required|max:100',
                'description' => 'required|max:1000',
                'price' => 'required|numeric|min:0',
                'category_id' => 'required|integer'
            ])
        ]
    ]);
    $routes->get('/edit-product/{id:\d+}', ['Src\Controllers\ProductController', 'showEditProductForm']);
    $routes->post('/edit-product/{id:\d+}', [
        'handler' => ['Src\Controllers\ProductController', 'updateProduct'],
        'middleware' => [
            new ValidationSanitizationMiddleware([
                'name' => 'max:100',
                'description' => 'max:1000',
                'price' => 'numeric|min:0',
                'category_id' => 'integer'
            ])
        ]
    ]);
    $routes->post('/delete-product/{id:\d+}', ['Src\Controllers\ProductController', 'deleteProduct']);
    $routes->post('/crop-thumbnail', ['Src\Controllers\ProductController', 'cropThumbnail']);

    // Asset routes
    $routes->post('/assets/upload', [
        'handler' => ['Src\Controllers\AssetController', 'upload'],
        'middleware' => [
            new ValidationSanitizationMiddleware([
                'file' => 'required|file|max:10000', // 10MB max file size
                'user_id' => 'required|integer'
            ])
        ]
    ]);
    $routes->get('/assets/download/{id}', ['Src\Controllers\AssetController', 'download']);
    $routes->get('/assets/stream/{id}', ['Src\Controllers\AssetController', 'streamDownload']);
    $routes->put('/assets/{id}', [
        'handler' => ['Src\Controllers\AssetController', 'update'],
        'middleware' => [
            new ValidationSanitizationMiddleware([
                'file_name' => 'max:255',
                // Add other fields that can be updated, with their validation rules
            ])
        ]
    ]);
    $routes->delete('/assets/{id}', ['Src\Controllers\AssetController', 'delete']);
    $routes->get('/assets/{id}/download-history', ['Src\Controllers\AssetController', 'getDownloadHistory']);
    $routes->post('/assets/crop', [
        'handler' => ['Src\Controllers\AssetController', 'cropImage'],
        'middleware' => [
            new ValidationSanitizationMiddleware([
                'asset_id' => 'required|integer',
                'width' => 'required|integer|min:1',
                'height' => 'required|integer|min:1',
                'x' => 'required|integer|min:0',
                'y' => 'required|integer|min:0'
            ])
        ]
    ]);

    // Seller Dashboard routes
    $routes->get('/seller/dashboard', ['Src\Controllers\SellerDashboardController', 'index']);
    $routes->get('/seller/products', ['Src\Controllers\SellerDashboardController', 'productList']);
    $routes->get('/seller/orders', ['Src\Controllers\SellerDashboardController', 'orderList']);

    // File Delivery routes
    $routes->get('/generate-download/{fileId:\d+}', ['Src\Controllers\FileDeliveryController', 'generateDownloadLink']);
    $routes->get('/download/{fileId:\d+}/{token}', ['Src\Controllers\FileDeliveryController', 'serveFile']);

    // User Panel routes
    $routes->get('/user-panel/purchases', ['Src\Controllers\UserPanelController', 'purchases']);
    $routes->get('/user-panel/generate-download/{fileId:\d+}', ['Src\Controllers\UserPanelController', 'generateDownloadLink']);
    $routes->get('/user-panel/download/{fileId:\d+}/{token}', ['Src\Controllers\FileDeliveryController', 'serveFile']);

    // License routes
    $routes->get('/licenses', ['Src\Controllers\LicenseController', 'index']);
    $routes->get('/licenses/create', ['Src\Controllers\LicenseController', 'create']);
    $routes->post('/licenses/create', ['Src\Controllers\LicenseController', 'create']);
    $routes->get('/licenses/edit/{id:\d+}', ['Src\Controllers\LicenseController', 'edit']);
    $routes->post('/licenses/edit/{id:\d+}', ['Src\Controllers\LicenseController', 'edit']);
    $routes->post('/licenses/delete/{id:\d+}', ['Src\Controllers\LicenseController', 'delete']);

    // Post routes
    $routes->get('/posts', ['Src\Controllers\PostController', 'index']);
    $routes->get('/posts/{id:\d+}', ['Src\Controllers\PostController', 'view']);
    $routes->post('/posts', [
        'handler' => ['Src\Controllers\PostController', 'create'],
        'middleware' => [
            new ValidationSanitizationMiddleware([
                'title' => 'required|max:255',
                'content' => 'required|max:65535',
                'user_id' => 'required|integer'
            ])
        ]
    ]);
    $routes->put('/posts/{id:\d+}', [
        'handler' => ['Src\Controllers\PostController', 'update'],
        'middleware' => [
            new ValidationSanitizationMiddleware([
                'title' => 'max:255',
                'content' => 'max:65535'
            ])
        ]
    ]);
    $routes->delete('/posts/{id:\d+}', ['Src\Controllers\PostController', 'delete']);
    $routes->post('/posts/{id:\d+}/schedule', [
        'handler' => ['Src\Controllers\PostController', 'schedulePost'],
        'middleware' => [
            new ValidationSanitizationMiddleware([
                'scheduled_at' => 'required|date'
            ])
        ]
    ]);
    $routes->post('/posts/{id:\d+}/toggle-featured', ['Src\Controllers\PostController', 'toggleFeatured']);
    $routes->get('/posts/featured', ['Src\Controllers\PostController', 'getFeaturedPosts']);

    // Comment routes
    $routes->post('/comments', [
        'handler' => ['Src\Controllers\CommentController', 'create'],
        'middleware' => [
            new ValidationSanitizationMiddleware([
                'post_id' => 'required|integer',
                'user_id' => 'required|integer',
                'content' => 'required|max:1000'
            ])
        ]
    ]);
    $routes->put('/comments/{id}', [
        'handler' => ['Src\Controllers\CommentController', 'edit'],
        'middleware' => [
            new ValidationSanitizationMiddleware([
                'content' => 'required|max:1000'
            ])
        ]
    ]);
    $routes->delete('/comments/{id}', ['Src\Controllers\CommentController', 'delete']);
    $routes->post('/comments/{id}/vote/{type}', [
        'handler' => ['Src\Controllers\CommentController', 'vote'],
        'middleware' => [
            new ValidationSanitizationMiddleware([
                'type' => 'required|in:up,down'
            ])
        ]
    ]);
    $routes->get('/posts/{postId}/comments', ['Src\Controllers\CommentController', 'getThreadedComments']);

    // Profile routes
    $routes->get('/profile/{id:\d+}', ['Src\Controllers\ProfileController', 'show']);
    $routes->get('/profile/edit', ['Src\Controllers\ProfileController', 'edit']);
    $routes->post('/profile/update', ['Src\Controllers\ProfileController', 'update']);

    // Portfolio routes
    $routes->get('/portfolio/manage', ['Src\Controllers\PortfolioController', 'manage']);
    $routes->post('/portfolio/add', ['Src\Controllers\PortfolioController', 'add']);
    $routes->post('/portfolio/update', ['Src\Controllers\PortfolioController', 'update']);
    $routes->post('/portfolio/delete', ['Src\Controllers\PortfolioController', 'delete']);

    // Message routes
    $routes->get('/messages', ['Src\Controllers\MessageController', 'index']);
    $routes->get('/messages/compose', ['Src\Controllers\MessageController', 'compose']);
    $routes->post('/messages/send', ['Src\Controllers\MessageController', 'send']);
    $routes->get('/messages/view/{id:\d+}', ['Src\Controllers\MessageController', 'view']);

    // Follow routes
    $routes->post('/follow', ['Src\Controllers\ProfileController', 'follow']);
    $routes->post('/unfollow/{id:\d+}', ['Src\Controllers\FollowController', 'unfollow']);

    // Moderation routes
    $routes->get('/moderation', ['Src\Controllers\ModerationController', 'index']);
    $routes->get('/moderation/review/{id:\d+}', ['Src\Controllers\ModerationController', 'reviewReport']);
    $routes->post('/moderation/approve/{id:\d+}', ['Src\Controllers\ModerationController', 'approveContent']);
    $routes->post('/moderation/remove/{id:\d+}', ['Src\Controllers\ModerationController', 'removeContent']);
    $routes->post('/moderation/warn/{id:\d+}', ['Src\Controllers\ModerationController', 'warnUser']);
    $routes->post('/moderation/suspend/{id:\d+}/{days:\d+}', ['Src\Controllers\ModerationController', 'suspendUser']);

    // Markdown rendering route
    $routes->post('/api/render-markdown', ['Src\Controllers\MarkdownController', 'render']);

    // Seller routes
    $routes->get('/seller/{id:\d+}', ['Src\Controllers\SellerController', 'getSellerById']);
    $routes->get('/seller/{id:\d+}/products', ['Src\Controllers\SellerController', 'getSellerProducts']);
    $routes->get('/seller/{id:\d+}/offers', ['Src\Controllers\SellerController', 'getSellerOffers']);
    $routes->put('/seller/products/{id:\d+}/related', [
        'handler' => ['Src\Controllers\SellerController', 'updateRelatedProducts'],
        'middleware' => [
            new ValidationSanitizationMiddleware([
                'related_product_ids' => 'required|array|max:10'
            ])
        ]
    ]);
    $routes->put('/seller/{id:\d+}/services', [
        'handler' => ['Src\Controllers\SellerController', 'updateServices'],
        'middleware' => [
            new ValidationSanitizationMiddleware([
                'services' => 'required|array',
                'services.*.name' => 'required|max:100',
                'services.*.description' => 'required|max:500'
            ])
        ]
    ]);
    $routes->post('/seller/{id:\d+}/assets', [
        'handler' => ['Src\Controllers\SellerController', 'addSellerAsset'],
        'middleware' => [
            new ValidationSanitizationMiddleware([
                'file' => 'required|file|max:10000' // 10MB max file size
            ])
        ]
    ]);
    $routes->delete('/seller/{sellerId:\d+}/assets/{assetId:\d+}', ['Src\Controllers\SellerController', 'removeSellerAsset']);
    $routes->get('/seller/{id:\d+}/assets', ['Src\Controllers\SellerController', 'getSellerAssets']);

    // AI routes
    $routes->get('/ai', [AIController::class, 'showInterface']);
    $routes->post('/ai/chat-completion', [
        'handler' => [AIController::class, 'chatCompletion'],
        'middleware' => [
            new ValidationSanitizationMiddleware([
                'prompt' => 'required|max:1000',
                'max_tokens' => 'integer|min:1|max:2048',
                'temperature' => 'numeric|min:0|max:1'
            ])
        ]
    ]);

    // Category routes
    $routes->get('/categories', ['Src\Controllers\CategoryController', 'getAllCategories']);
    $routes->get('/categories/{id:\d+}', ['Src\Controllers\CategoryController', 'getCategoryById']);
    $routes->post('/categories', [
        'handler' => ['Src\Controllers\CategoryController', 'createCategory'],
        'middleware' => [
            new ValidationSanitizationMiddleware([
                'name' => 'required|max:255',
                'description' => 'max:1000'
            ])
        ]
    ]);
    $routes->put('/categories/{id:\d+}', [
        'handler' => ['Src\Controllers\CategoryController', 'updateCategory'],
        'middleware' => [
            new ValidationSanitizationMiddleware([
                'name' => 'required|max:255',
                'description' => 'max:1000'
            ])
        ]
    ]);
    $routes->delete('/categories/{id:\d+}', ['Src\Controllers\CategoryController', 'deleteCategory']);

    // License routes
    $routes->get('/licenses', ['Src\Controllers\LicenseController', 'getUserLicenses']);
    $routes->get('/licenses/validate/{id}', ['Src\Controllers\LicenseController', 'validateLicense']);
    $routes->post('/licenses/extend/{id}', ['Src\Controllers\LicenseController', 'extendLicense']);
    $routes->post('/licenses/revoke/{id}', ['Src\Controllers\LicenseController', 'revokeLicense']);

    // Offer routes
    $routes->get('/offers', ['Src\Controllers\OfferController', 'index']);
    $routes->get('/offers/{id:\d+}', ['Src\Controllers\OfferController', 'show']);
    $routes->post('/offers', [
        'handler' => ['Src\Controllers\OfferController', 'create'],
        'middleware' => [
            new ValidationSanitizationMiddleware([
                'title' => 'required|max:100',
                'description' => 'required|max:1000',
                'price' => 'required|numeric|min:0',
                'category_id' => 'required|integer'
            ])
        ]
    ]);
    $routes->put('/offers/{id:\d+}', [
        'handler' => ['Src\Controllers\OfferController', 'update'],
        'middleware' => [
            new ValidationSanitizationMiddleware([
                'title' => 'max:100',
                'description' => 'max:1000',
                'price' => 'numeric|min:0',
                'category_id' => 'integer'
            ])
        ]
    ]);
    $routes->delete('/offers/{id:\d+}', ['Src\Controllers\OfferController', 'delete']);
    $routes->get('/offers/search', ['Src\Controllers\OfferController', 'search']);
    $routes->get('/offers/{id:\d+}/analytics', ['Src\Controllers\OfferController', 'analytics']);

    // Order routes
    $routes->post('/orders', [
        'handler' => ['Src\Controllers\OrderController', 'createOrder'],
        'middleware' => [
            new ValidationSanitizationMiddleware([
                'user_id' => 'required|integer',
                'items' => 'required|array',
                'items.*.product_id' => 'required|integer',
                'items.*.quantity' => 'required|integer|min:1',
                'total_amount' => 'required|numeric|min:0'
            ])
        ]
    ]);
    $routes->get('/orders/{id:\d+}', ['Src\Controllers\OrderController', 'getOrder']);
    $routes->get('/users/{id:\d+}/orders', ['Src\Controllers\OrderController', 'getUserOrders']);
    $routes->put('/orders/{id:\d+}/status', [
        'handler' => ['Src\Controllers\OrderController', 'updateOrderStatus'],
        'middleware' => [
            new ValidationSanitizationMiddleware([
                'status' => 'required|in:pending,processing,completed,cancelled'
            ])
        ]
    ]);
    $routes->get('/orders/{id:\d+}/licenses', ['Src\Controllers\OrderController', 'getOrderLicenses']);
};
