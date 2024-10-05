<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Interfaces\MessageServiceInterface;
use Src\Interfaces\UserServiceInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Src\Exceptions\ValidationException;
use Twig\Environment;

class MessageController extends BaseController
{
    protected $twig;
    protected $messageService;
    protected $userService;

    public function __construct(Environment $twig, MessageServiceInterface $messageService, UserServiceInterface $userService)
    {
        $this->twig = $twig;
        $this->messageService = $messageService;
        $this->userService = $userService;
    }

    public function index(Request $request, Response $response): void
    {
        $user = $request->user;
        $inbox = $this->messageService->getInboxMessages($user->id);
        $sent = $this->messageService->getSentMessages($user->id);

        $this->render($response, 'message/index', [
            'inbox' => $inbox,
            'sent' => $sent
        ]);
    }

    public function compose(Request $request, Response $response): void
    {
        $users = $this->userService->getAllUsers();

        $this->render($response, 'message/compose', [
            'users' => $users
        ]);
    }

    public function send(Request $request, Response $response): void
    {
        try {
            $data = $request->post;
            $senderId = $request->user->id;
            $messageData = [
                'sender_id' => $senderId,
                'recipient_id' => $data['recipient_id'],
                'subject' => $data['subject'],
                'content' => $data['content']
            ];

            $messageId = $this->messageService->sendMessage($messageData);

            $this->jsonResponse($response, ['success' => true, 'message' => 'Message sent successfully', 'message_id' => $messageId]);
        } catch (ValidationException $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred while sending the message'], 500);
        }
    }

    public function view(Request $request, Response $response, array $args): void
    {
        try {
            $messageId = (int) $args['id'];
            $userId = $request->user->id;
            $message = $this->messageService->getMessageById($messageId);

            if (!$message || ($message['sender_id'] !== $userId && $message['recipient_id'] !== $userId)) {
                $this->jsonResponse($response, ['error' => 'Message not found'], 404);
                return;
            }

            if ($message['recipient_id'] === $userId && !$message['read']) {
                $this->messageService->markAsRead($messageId);
            }

            $this->render($response, 'message/view', ['message' => $message]);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred while retrieving the message'], 500);
        }
    }

    public function delete(Request $request, Response $response, array $args): void
    {
        try {
            $messageId = (int) $args['id'];
            $userId = $request->user->id;
            $message = $this->messageService->getMessageById($messageId);

            if (!$message || ($message['sender_id'] !== $userId && $message['recipient_id'] !== $userId)) {
                $this->jsonResponse($response, ['error' => 'Message not found'], 404);
                return;
            }

            $success = $this->messageService->deleteMessage($messageId);

            if ($success) {
                $this->jsonResponse($response, ['success' => true, 'message' => 'Message deleted successfully']);
            } else {
                $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to delete message'], 400);
            }
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred while deleting the message'], 500);
        }
    }

    public function conversation(Request $request, Response $response, array $args): void
    {
        try {
            $userId = $request->user->id;
            $otherUserId = (int) $args['userId'];
            $conversation = $this->messageService->getConversation($userId, $otherUserId);

            $this->render($response, 'message/conversation', ['conversation' => $conversation, 'otherUserId' => $otherUserId]);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred while retrieving the conversation'], 500);
        }
    }

    public function unreadCount(Request $request, Response $response): void
    {
        try {
            $userId = $request->user->id;
            $count = $this->messageService->getUnreadCount($userId);

            $this->jsonResponse($response, ['success' => true, 'unread_count' => $count]);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred while retrieving unread count'], 500);
        }
    }
}