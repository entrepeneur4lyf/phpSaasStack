<?php

namespace App\Controllers;

use Twig\Environment;
use App\Services\MarkdownService;
use App\Services\AuthService;

class MarkdownController extends BaseController
{
    protected $twig;
    protected $markdownService;
    protected $authService;

    public function __construct(Environment $twig, MarkdownService $markdownService, AuthService $authService)
    {
        $this->twig = $twig;
        $this->markdownService = $markdownService;
        $this->authService = $authService;
    }

    public function preview()
    {
        $markdown = $this->request->getPost('markdown');
        $html = $this->markdownService->convertToHtml($markdown);
        return $this->response->setJSON(['html' => $html]);
    }

    public function editor()
    {
        return $this->twig->render('markdown/editor.twig');
    }

    public function save()
    {
        $user = $this->authService->getUser();
        $data = $this->request->getPost();
        $data['user_id'] = $user->id;

        $result = $this->markdownService->saveDocument($data);

        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => 'Document saved successfully']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Failed to save document']);
        }
    }

    public function load($id)
    {
        $user = $this->authService->getUser();
        $document = $this->markdownService->getDocumentById($id);

        if (!$document || $document->user_id !== $user->id) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Document not found']);
        }

        return $this->response->setJSON(['success' => true, 'document' => $document]);
    }
}