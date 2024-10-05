<?php
declare(strict_types=1);

namespace Src\Controllers;

use App\Models\User;
use Swoole\Http\Request;
use Swoole\Http\Response;

class AdminController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function dashboard(Request $request, Response $response): void
    {
        $stats = $this->getSystemStats();
        $recentUsers = $this->userModel->getRecentUsers(10);
        $recentActivity = $this->getRecentActivity();

        $dashboardData = [
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'recentActivity' => $recentActivity,
        ];

        $response->header('Content-Type', 'application/json');
        $response->end(json_encode($dashboardData));
    }

    public function userList(Request $request, Response $response): void
    {
        $page = (int)($request->get['page'] ?? 1);
        $limit = (int)($request->get['limit'] ?? 20);

        $users = $this->userModel->getPaginatedUsers($page, $limit);
        $totalUsers = $this->userModel->getTotalUsers();

        $response->header('Content-Type', 'application/json');
        $response->end(json_encode([
            'users' => $users,
            'totalUsers' => $totalUsers,
            'currentPage' => $page,
            'totalPages' => ceil($totalUsers / $limit),
        ]));
    }

    public function updateUserRole(Request $request, Response $response): void
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['userId']) || !isset($data['roleId'])) {
            $response->status(400);
            $response->end(json_encode(['error' => 'Invalid input']));
            return;
        }

        $result = $this->userModel->updateRole($data['userId'], $data['roleId']);

        if ($result) {
            $response->end(json_encode(['message' => 'User role updated successfully']));
        } else {
            $response->status(500);
            $response->end(json_encode(['error' => 'Failed to update user role']));
        }
    }

    public function getSystemStats(): array
    {
        // Implement logic to fetch system-wide statistics
        // This is a placeholder implementation
        return [
            'totalUsers' => $this->userModel->getTotalUsers(),
            'activeUsers' => $this->userModel->getActiveUsers(),
            'totalInferences' => 1000,
            'averageResponseTime' => '200ms',
        ];
    }

    public function getRecentActivity(): array
    {
        // Implement logic to fetch recent system-wide activity
        // This is a placeholder implementation
        return [
            ['type' => 'new_user', 'date' => '2023-05-01 10:00:00'],
            ['type' => 'inference', 'date' => '2023-05-01 09:55:00'],
            ['type' => 'subscription_upgrade', 'date' => '2023-05-01 09:30:00'],
        ];
    }

    
    public function messageCategories()
    {
        $categories = $this->userModel->getMessageCategories();
        return view('admin/message_categories', ['categories' => $categories]);
    }

    public function addMessageCategory()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            if ($name) {
                $db = Database::getInstance();
                $stmt = $db->prepare("INSERT INTO message_categories (name) VALUES (?)");
                if ($stmt->execute([$name])) {
                    $_SESSION['success_message'] = "Category added successfully.";
                } else {
                    $_SESSION['error_message'] = "Failed to add category.";
                }
            }
        }
        return redirect('/admin/message-categories');
    }
}