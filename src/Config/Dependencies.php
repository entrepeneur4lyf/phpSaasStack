<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Src\Services\Interfaces\ThemeServiceInterface;
use Src\Interfaces\CategoryRepositoryInterface;
use Src\Interfaces\ProductRepositoryInterface;
use Src\Interfaces\PortfolioServiceInterface;
use Src\Interfaces\CategoryServiceInterface;
use Src\Interfaces\ProductServiceInterface;
use Src\Interfaces\ProfileServiceInterface;
use Src\Interfaces\OpenAIWrapperInterface;
use Src\Interfaces\SellerServiceInterface;
use Src\Interfaces\CacheServiceInterface;
use Src\Interfaces\RequestQueueServiceInterface;
use Src\Interfaces\OfferServiceInterface;
use Src\Interfaces\PiAPIClientInterface;
use Src\Repositories\CategoryRepository;
use Src\Interfaces\UserServiceInterface;
use Src\Interfaces\AuthServiceInterface;
use Src\Interfaces\JWTServiceInterface;
use Src\Repositories\ProductRepository;
use Src\Controllers\ProfileController;
use Src\Interfaces\AIServiceInterface;
use Src\Services\PortfolioService;
use Monolog\Handler\StreamHandler;
use Src\Services\FileCacheService;
use Src\Middleware\JWTMiddleware;
use Src\Services\CategoryService;
use Src\Services\DatabaseService;
use Src\Services\ProductService;
use Src\Services\ProfileService;
use Src\Services\OpenAIWrapper;
use Src\Services\SellerService;
use Src\Services\ThemeService;
use Src\Services\EmailService;
use Src\Services\RequestQueueService;
use Src\Services\OfferService;
use Src\Services\PiAPIClient;
use Src\Services\AuthService;
use Src\Services\UserService;
use Src\Services\JWTService;
use Src\Services\AIService\AIService;
use Src\Core\ErrorHandler;
use Src\Models\Portfolio;
use Src\Models\Product;
use Src\Models\Seller;
use Src\Models\Offer;
use Monolog\Logger;
use Src\Interfaces\CommentServiceInterface;
use Src\Services\CommentService;
use Src\Models\Comment;
use Src\Interfaces\PostServiceInterface;
use Src\Services\PostService;
use Src\Models\Post;
use Src\Interfaces\MessageServiceInterface;
use Src\Services\MessageService;
use Src\Models\Message;
use Src\Interfaces\FollowServiceInterface;
use Src\Services\FollowService;
use Src\Models\Follow;
use Src\Interfaces\ModerationServiceInterface;
use Src\Services\ModerationService;
use Src\Models\Moderation;
use Src\Interfaces\LicenseServiceInterface;
use Src\Services\LicenseService;
use Src\Models\License;
use Src\Interfaces\AssetServiceInterface;
use Src\Services\AssetService;
use Src\Models\Asset;
use Src\Interfaces\FileDeliveryServiceInterface;
use Src\Services\FileDeliveryService;
use Src\Models\Download;
use Src\Models\Report;
use Src\Models\ModerationLog;
use Src\Interfaces\OrderServiceInterface;
use Src\Models\Order;
use Src\Controllers\AssetController;
use Src\Commands\PublishScheduledPosts;
use Src\Commands\ProcessAIRequests;
use Src\Core\CacheManager;

return function (ContainerBuilder $containerBuilder, array $config) {
    // AI Services
    $containerBuilder->register(OpenAIWrapperInterface::class, OpenAIWrapper::class)
        ->setArguments([$config['api_keys']['openai']]);
    
    $containerBuilder->register(PiAPIClientInterface::class, PiAPIClient::class)
        ->setFactory([PiAPIClient::class, 'create'])
        ->setArguments([$config['api_keys']['piapi']]);
    
    $containerBuilder->register(RequestQueueServiceInterface::class, RequestQueueService::class);
    
    $containerBuilder->register(AIServiceInterface::class, AIService::class)
        ->setArguments([
            new Reference(OpenAIWrapperInterface::class),
            new Reference(RequestQueueServiceInterface::class)
        ]);

    // Theme Service
    $containerBuilder->register(ThemeServiceInterface::class, ThemeService::class);

    // User and Auth Services
    $containerBuilder->register(UserServiceInterface::class, UserService::class);
    $containerBuilder->register(JWTServiceInterface::class, JWTService::class)
        ->setArguments([$config['jwt_secret'], $config['jwt_algorithm'] ?? 'HS256']);

    $containerBuilder->register(AuthServiceInterface::class, AuthService::class)
        ->setArguments([
            new Reference(UserServiceInterface::class),
            new Reference(JWTServiceInterface::class)
        ]);

    // Email Service
    $containerBuilder->register(EmailService::class);

    // JWT Service and Middleware
    $containerBuilder->register(JWTMiddleware::class)
        ->setArguments([new Reference(JWTServiceInterface::class)]);

    // Database Service
    $containerBuilder->register(DatabaseService::class)
        ->setArguments([$config['database']]);

    // Category Service and Repository
    $containerBuilder->register(CategoryRepositoryInterface::class, CategoryRepository::class);
    $containerBuilder->register(CategoryServiceInterface::class, CategoryService::class)
        ->setArguments([new Reference(CategoryRepositoryInterface::class)]);

    // Product Service and Repository
    $containerBuilder->register(ProductRepositoryInterface::class, ProductRepository::class);
    $containerBuilder->register(ProductServiceInterface::class, ProductService::class)
        ->setArguments([
            new Reference(Product::class),
            new Reference(LicenseServiceInterface::class)
        ]);

    $containerBuilder->register(Product::class);

    // Logger
    $containerBuilder->register(Logger::class)
        ->addArgument('app')
        ->addMethodCall('pushHandler', [new Reference('log_handler')]);

    $containerBuilder->register('log_handler', StreamHandler::class)
        ->setArguments(['../logs/app.log', Logger::DEBUG]);

    // Error Handler
    $containerBuilder->register(ErrorHandler::class)
        ->setArguments([new Reference(Logger::class)]);

    // Cache Service
    $containerBuilder->register(CacheServiceInterface::class, FileCacheService::class)
        ->setArguments([$config['cache_dir'] ?? '../cache']);

    // Seller Service
    $containerBuilder->register(SellerServiceInterface::class, SellerService::class)
        ->setArguments([new Reference(Seller::class)]);

    $containerBuilder->register(Seller::class);

    // Portfolio Service
    $containerBuilder->register(PortfolioServiceInterface::class, PortfolioService::class)
        ->setArguments([new Reference(Portfolio::class)]);

    $containerBuilder->register(Portfolio::class);

    // Profile Service and Controller
    $containerBuilder->register(ProfileServiceInterface::class, ProfileService::class);
    $containerBuilder->register(ProfileController::class)
        ->setArguments([
            new Reference(UserServiceInterface::class),
            new Reference(ProfileServiceInterface::class),
            new Reference(PortfolioServiceInterface::class),
            new Reference(OfferServiceInterface::class),
            new Reference(MessageServiceInterface::class),
            new Reference(PostServiceInterface::class),
        ]);

    // Offer Service
    $containerBuilder->register(OfferServiceInterface::class, OfferService::class)
        ->setArguments([new Reference(Offer::class)]);

    $containerBuilder->register(Offer::class);

    // Comment Service
    $containerBuilder->register(CommentServiceInterface::class, CommentService::class)
        ->setArguments([new Reference(Comment::class)]);

    $containerBuilder->register(Comment::class);

    // Post Service
    $containerBuilder->register(PostServiceInterface::class, PostService::class)
        ->setArguments([new Reference(Post::class)]);

    $containerBuilder->register(Post::class);

    // Message Service
    $containerBuilder->register(MessageServiceInterface::class, MessageService::class)
        ->setArguments([new Reference(Message::class)]);

    $containerBuilder->register(Message::class);

    // Follow Service
    $containerBuilder->register(FollowServiceInterface::class, FollowService::class)
        ->setArguments([new Reference(Follow::class)]);

    $containerBuilder->register(Follow::class);

    // Moderation Service
    $containerBuilder->register(ModerationServiceInterface::class, ModerationService::class)
        ->setArguments([
            new Reference(Report::class),
            new Reference(ModerationLog::class),
            new Reference(PostServiceInterface::class),
            new Reference(CommentServiceInterface::class),
            new Reference(UserServiceInterface::class)
        ]);

    $containerBuilder->register(Report::class);
    $containerBuilder->register(ModerationLog::class);

    // License Service
    $containerBuilder->register(LicenseServiceInterface::class, LicenseService::class)
        ->setArguments([new Reference(License::class)]);

    $containerBuilder->register(License::class);

    // Asset Service
    $containerBuilder->register(AssetServiceInterface::class, AssetService::class)
        ->setArguments([
            new Reference(Asset::class),
            new Reference(FileDeliveryServiceInterface::class)
        ]);

    $containerBuilder->register(Asset::class);

    // File Delivery Service
    $containerBuilder->register(FileDeliveryServiceInterface::class, FileDeliveryService::class)
        ->setArguments([
            new Reference(Asset::class),
            new Reference(Download::class)
        ]);

    $containerBuilder->register(Asset::class);
    $containerBuilder->register(Download::class);

    // Order Service
    $containerBuilder->register(OrderServiceInterface::class, OrderService::class)
        ->setArguments([
            new Reference(Order::class),
            new Reference(LicenseServiceInterface::class)
        ]);

    $containerBuilder->register(Order::class);

    // Ensure all controllers are autowired
    $containerBuilder->autowire(Src\Controllers\AIController::class);
    $containerBuilder->autowire(Src\Controllers\UserController::class);
    $containerBuilder->autowire(Src\Controllers\AuthController::class);
    $containerBuilder->autowire(Src\Controllers\AssetController::class);
    $containerBuilder->autowire(Src\Controllers\ProductController::class);
    $containerBuilder->autowire(Src\Controllers\SellerDashboardController::class);
    $containerBuilder->autowire(Src\Controllers\FileDeliveryController::class);
    $containerBuilder->autowire(Src\Controllers\UserPanelController::class);
    $containerBuilder->autowire(Src\Controllers\LicenseController::class);
    $containerBuilder->autowire(Src\Controllers\PostController::class);
    $containerBuilder->autowire(Src\Controllers\CommentController::class);
    $containerBuilder->autowire(Src\Controllers\ProfileController::class);
    $containerBuilder->autowire(Src\Controllers\PortfolioController::class);
    $containerBuilder->autowire(Src\Controllers\MessageController::class);
    $containerBuilder->autowire(Src\Controllers\FollowController::class);
    $containerBuilder->autowire(Src\Controllers\ModerationController::class);
    $containerBuilder->autowire(Src\Controllers\MarkdownController::class);
    $containerBuilder->autowire(Src\Controllers\SellerController::class);
    $containerBuilder->autowire(Src\Controllers\HomeController::class);

    // Add error handling for service creation
    $containerBuilder->register('exception_handler', function ($e) {
        // Log the error and handle it appropriately
        error_log($e->getMessage());
        // You might want to throw a custom exception or handle it in some other way
    });

    // Error Handler
    $containerBuilder->register(ErrorHandler::class)
        ->setArguments([new Reference(Logger::class)]);

    $containerBuilder->register(AssetController::class)
        ->setArguments([
            new Reference(AssetServiceInterface::class),
            new Reference(FileDeliveryServiceInterface::class)
        ]);

    $containerBuilder->register(PublishScheduledPosts::class)
        ->setArguments([new Reference(PostServiceInterface::class)]);

    $containerBuilder->register(ProcessAIRequests::class)
        ->setArguments([
            new Reference(AIServiceInterface::class),
            new Reference(RequestQueueServiceInterface::class)
        ]);

    // Register CacheManager
    $containerBuilder->register(CacheManager::class)
        ->setArguments([
            new Reference('cache.default')
        ]);

    // Register cache driver (assuming you're using the default driver from config)
    $containerBuilder->register('cache.default', $config['cache']['default']['driver'])
        ->setArguments([
            $config['cache']['default']
        ]);
};