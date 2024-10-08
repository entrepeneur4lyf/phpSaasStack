<?php

namespace Src\Controllers;

use Src\Core\TwigRenderer;
use Src\Services\MarkdownService;
use Src\Services\AuthService;
use Swoole\Http\Request;
use Swoole\Http\Response;

class MarkdownController extends BaseController
{
    protected MarkdownService $markdownService;
    protected AuthService $authService;

    public function __construct(TwigRenderer $twigRenderer, MarkdownService $markdownService, AuthService $authService)
    {
        parent::__construct($twigRenderer);
        $this->markdownService = $markdownService;
        $this->authService = $authService;
    }

    public function preview(Request $request, Response $response)
    {
        $markdown = $request->post['markdown'] ?? '';
        $html = $this->markdownService->convertToHtml($markdown);
        $this->jsonResponse($response, ['html' => $html]);
    }

    public function editor(Request $request, Response $response)
    {
        $this->render($response, 'markdown/editor');
    }

    public function save(Request $request, Response $response)
    {
        $user = $this->authService->getUser();
        $data = $request->post;
        $data['user_id'] = $user->id;

        $result = $this->markdownService->saveDocument($data);

        if ($result) {
            $this->jsonResponse($response, ['success' => true, 'message' => 'Document saved successfully']);
        } else {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to save document'], 500);
        }
    }

    public function load(Request $request, Response $response, $id)
    {
        $user = $this->authService->getUser();
        $document = $this->markdownService->getDocumentById($id);

        if (!$document || $document->user_id !== $user->id) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Document not found'], 404);
        } else {
            $this->jsonResponse($response, ['success' => true, 'document' => $document]);
        }
    }
}
