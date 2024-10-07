<?php

declare(strict_types=1);

namespace Src\Services;

use Src\Interfaces\EmailServiceInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService implements EmailServiceInterface
{
    private MailerInterface $mailer;
    private array $config;

    public function __construct(MailerInterface $mailer, array $config)
    {
        $this->mailer = $mailer;
        $this->config = $config;
    }

    public function sendEmail(string $to, string $subject, string $body, array $attachments = []): void
    {
        $email = (new Email())
            ->from($this->config['from_email'])
            ->to($to)
            ->subject($subject)
            ->html($body);

        foreach ($attachments as $attachment) {
            $email->attachFromPath($attachment['path'], $attachment['name']);
        }

        $this->mailer->send($email);
    }

    public function sendTemplatedEmail(string $to, string $subject, string $template, array $context, array $attachments = []): void
    {
        $body = $this->renderTemplate($template, $context);
        $this->sendEmail($to, $subject, $body, $attachments);
    }

    private function renderTemplate(string $template, array $context): string
    {
        // Implement template rendering logic here
        // This could use Twig or another template engine
        // For now, we'll use a simple placeholder replacement
        $content = file_get_contents($this->config['template_path'] . '/' . $template . '.html');
        foreach ($context as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        return $content;
    }
}