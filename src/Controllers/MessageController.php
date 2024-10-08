<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Interfaces\MessageServiceInterface;
use Src\Interfaces\UserServiceInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Src\Exceptions\ValidationException;
use Src\Core\TwigRenderer;
use Src\Utils\FileUploadHandler;

class MessageController extends BaseController
{
    protected MessageServiceInterface $messageService;
    protected UserServiceInterface $userService;
    protected FileUploadHandler $fileUploadHandler;

    public function __construct(
        TwigRenderer $twigRenderer,
        MessageServiceInterface $messageService,
        UserServiceInterface $userService,
        FileUploadHandler $fileUploadHandler
    ) {
        parent::__construct($twigRenderer);
        $this->messageService = $messageService;
        $this->userService = $userService;
        $this->fileUploadHandler = $fileUploadHandler;
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
                'content' => $data['content'],
                'parent_id' => $data['parent_id'] ?? null // For message threading
            ];

            // Handle file attachments
            $attachments = [];
            if (isset($request->files['attachments'])) {
                foreach ($request->files['attachments'] as $file) {
                    $attachmentPath = $this->fileUploadHandler->handleUpload($file);
                    $attachments[] = $attachmentPath;
                }
            }
            $messageData['attachments'] = $attachments;

            $messageId = $this->messageService->sendMessage($messageData);

            // Send real-time notification
            $this->webSocketController->sendNotification(
                $request->wsServer,
                $data['recipient_id'],
                'newMessage',
                [
                    'messageId' => $messageId,
                    'sender' => $request->user->name,
                    'subject' => $data['subject']
                ]
            );

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

            // Get threaded messages
            $thread = $this->messageService->getMessageThread($messageId);

            $this->render($response, 'message/view', ['message' => $message, 'thread' => $thread]);
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

    public function search(Request $request, Response $response): void
    {
        try {
            $userId = $request->user->id;
            $query = $request->get['q'] ?? '';
            $page = (int) ($request->get['page'] ?? 1);
            $limit = (int) ($request->get['limit'] ?? 20);

            $results = $this->messageService->searchMessages($userId, $query, $page, $limit);

            $this->jsonResponse($response, ['success' => true, 'results' => $results]);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred while searching messages'], 500);
        }
    }

    public function downloadAttachment(Request $request, Response $response, array $args): void
    {
        try {
            $attachmentId = (int) $args['id'];
            $userId = $request->user->id;

            $attachmentPath = $this->messageService->getAttachmentPath($attachmentId, $userId);

            if (!$attachmentPath) {
                $this->jsonResponse($response, ['error' => 'Attachment not found'], 404);
                return;
            }

            $response->header('Content-Type', mime_content_type($attachmentPath));
            $response->header('Content-Disposition', 'attachment; filename="' . basename($attachmentPath) . '"');
            $response->sendfile($attachmentPath);
        } catch (\Exception $e) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'An error occurred while downloading the attachment'], 500);
        }
    }
}
