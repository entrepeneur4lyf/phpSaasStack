<?php

namespace Src\Middleware;

use Src\Services\ValidationService;
use Src\Services\SanitizationService;
use Swoole\Http\Request;
use Swoole\Http\Response;

class ValidationSanitizationMiddleware
{
    private array $rules;
    private ValidationService $validationService;
    private SanitizationService $sanitizationService;

    public function __construct(array $rules, ValidationService $validationService, SanitizationService $sanitizationService)
    {
        $this->rules = $rules;
        $this->validationService = $validationService;
        $this->sanitizationService = $sanitizationService;
    }

    public function handle(Request $request, Response $response, callable $next): void
    {
        $data = array_merge($request->get ?? [], $request->post ?? []);

        $errors = $this->validationService->validate($data, $this->rules);
        if (!empty($errors)) {
            $response->status(422);
            $response->header('Content-Type', 'application/json');
            $response->end(json_encode(['errors' => $errors]));
            return;
        }

        $request->get = $this->sanitizationService->sanitize($request->get ?? []);
        $request->post = $this->sanitizationService->sanitize($request->post ?? []);

        $next($request, $response);
    }
}