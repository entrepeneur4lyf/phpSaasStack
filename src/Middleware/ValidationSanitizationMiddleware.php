<?php
declare(strict_types=1);

namespace Src\Middleware;

use Src\Utils\Validator;
use Src\Utils\InputSanitizer;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Src\Exceptions\ValidationException;

class ValidationSanitizationMiddleware
{
    private array $rules;

    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    public function handle(Request $request, Response $response, callable $next): void
    {
        $data = array_merge($request->get ?? [], $request->post ?? []);

        try {
            $this->validate($data, $this->rules);

            // Sanitize inputs
            $request->get = InputSanitizer::sanitizeArray($request->get ?? []);
            $request->post = InputSanitizer::sanitizeArray($request->post ?? []);

            $next($request, $response);
        } catch (ValidationException $e) {
            $response->status(422);
            $response->header('Content-Type', 'application/json');
            $response->end(json_encode(['errors' => $e->getErrors()]));
        }
    }

    private function validate(array $data, array $rules): void
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            if (!isset($data[$field]) && strpos($rule, 'required') !== false) {
                $errors[$field] = ucfirst($field) . ' is required.';
            } elseif (isset($data[$field])) {
                if (strpos($rule, 'email') !== false && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = 'Invalid email format.';
                }
                if (preg_match('/max:(\d+)/', $rule, $matches)) {
                    $maxLength = (int) $matches[1];
                    if (strlen($data[$field]) > $maxLength) {
                        $errors[$field] = ucfirst($field) . ' must not exceed ' . $maxLength . ' characters.';
                    }
                }
                // Add other validation rules here...
            }
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
}