<?php

declare(strict_types=1);

namespace Src\Config;

class Config
{
    // ... existing code ...

    public function getDefaultConfig(): array
    {
        return [
            // ... existing config ...
            'error_reporting' => [
                'email_to' => 'admin@example.com',
                'slack_webhook_url' => 'your-slack-webhook-url',
            ],
            'mail' => [
                'host' => 'smtp.example.com',
                'port' => 587,
                'username' => 'your-smtp-username',
                'password' => 'your-smtp-password',
            ],
            'rollbar' => [
                'access_token' => 'your-rollbar-access-token',
            ],
            'app' => [
                'environment' => 'production', // or 'development', 'staging', etc.
                // ... other app settings ...
            ],
        ];
    }
}