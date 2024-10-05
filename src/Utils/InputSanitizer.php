<?php
declare(strict_types=1);

namespace Src\Utils;

class InputSanitizer
{
    public static function sanitizeString(string $input): string
    {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public static function sanitizeEmail(string $email): string
    {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }

    public static function sanitizeInt(string $input): int
    {
        return (int)filter_var($input, FILTER_SANITIZE_NUMBER_INT);
    }

    public static function sanitizeFloat(string $input): float
    {
        return (float)filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    public static function sanitizeArray(array $input): array
    {
        $sanitized = [];
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = self::sanitizeArray($value);
            } elseif (is_string($value)) {
                $sanitized[$key] = self::sanitizeString($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    public static function sanitizeOutput(string $output): string
    {
        return htmlspecialchars($output, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}