<?php

declare(strict_types=1);

namespace Src\Utils;

class Validator
{
    private array $errors = [];

    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            foreach ($fieldRules as $rule) {
                $this->applyRule($field, $data[$field] ?? null, $rule);
            }
        }

        return empty($this->errors);
    }

    private function applyRule(string $field, $value, string $rule): void
    {
        [$ruleName, $ruleValue] = explode(':', $rule . ':');

        switch ($ruleName) {
            case 'required':
                if (empty($value)) {
                    $this->addError($field, "{$field} is required");
                }
                break;
            case 'min':
                if (strlen($value) < (int)$ruleValue) {
                    $this->addError($field, "{$field} must be at least {$ruleValue} characters");
                }
                break;
            case 'max':
                if (strlen($value) > (int)$ruleValue) {
                    $this->addError($field, "{$field} must not exceed {$ruleValue} characters");
                }
                break;
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "{$field} must be a valid email address");
                }
                break;
            // Add more validation rules as needed
        }
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}