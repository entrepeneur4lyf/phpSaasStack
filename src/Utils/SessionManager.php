<?php

declare(strict_types=1);

namespace Src\Utils;

use Swoole\Http\Request;
use Swoole\Http\Response;

class SessionManager
{
    private const SESSION_NAME = 'SECURE_SESSION_ID';
    private const SESSION_LIFETIME = 3600; // 1 hour
    private string $sessionPath;
    private string $encryptionKey;

    public function __construct(string $sessionPath, string $encryptionKey)
    {
        $this->sessionPath = $sessionPath;
        $this->encryptionKey = $encryptionKey;
    }

    public function startSession(Request $request, Response $response): array
    {
        $sessionId = $this->getSessionIdFromCookie($request);

        if (!$sessionId || !$this->isValidSession($sessionId)) {
            $sessionId = $this->createSession();
            $this->setSessionCookie($response, $sessionId);
        }

        return $this->loadSessionData($sessionId);
    }

    public function saveSession(array $data, Response $response): void
    {
        $sessionId = $this->getSessionIdFromCookie($response);
        $this->saveSessionData($sessionId, $data);
        $this->regenerateSession($sessionId, $response);
    }

    public function destroySession(Response $response): void
    {
        $sessionId = $this->getSessionIdFromCookie($response);
        if ($sessionId) {
            $this->deleteSessionFile($sessionId);
            $this->removeSessionCookie($response);
        }
    }

    private function createSession(): string
    {
        return bin2hex(random_bytes(32));
    }

    private function isValidSession(string $sessionId): bool
    {
        $sessionFile = $this->getSessionFilePath($sessionId);
        return file_exists($sessionFile) && (time() - filemtime($sessionFile) < self::SESSION_LIFETIME);
    }

    private function loadSessionData(string $sessionId): array
    {
        $sessionFile = $this->getSessionFilePath($sessionId);
        if (file_exists($sessionFile)) {
            $encryptedData = file_get_contents($sessionFile);
            $decryptedData = $this->decrypt($encryptedData);
            return json_decode($decryptedData, true) ?? [];
        }
        return [];
    }

    private function saveSessionData(string $sessionId, array $data): void
    {
        $sessionFile = $this->getSessionFilePath($sessionId);
        $encryptedData = $this->encrypt(json_encode($data));
        file_put_contents($sessionFile, $encryptedData);
    }

    private function regenerateSession(string $oldSessionId, Response $response): void
    {
        $newSessionId = $this->createSession();
        $oldSessionFile = $this->getSessionFilePath($oldSessionId);
        $newSessionFile = $this->getSessionFilePath($newSessionId);

        if (file_exists($oldSessionFile)) {
            rename($oldSessionFile, $newSessionFile);
        }

        $this->setSessionCookie($response, $newSessionId);
    }

    private function getSessionFilePath(string $sessionId): string
    {
        return $this->sessionPath . '/' . $sessionId . '.sess';
    }

    private function getSessionIdFromCookie(Request $request): ?string
    {
        $sessionCookie = $request->cookie[self::SESSION_NAME] ?? null;
        return $sessionCookie ? $this->decrypt($sessionCookie) : null;
    }

    private function setSessionCookie(Response $response, string $sessionId): void
    {
        $encryptedSessionId = $this->encrypt($sessionId);
        $response->cookie(self::SESSION_NAME, $encryptedSessionId, time() + self::SESSION_LIFETIME, '/', '', true, true);
    }

    private function removeSessionCookie(Response $response): void
    {
        $response->cookie(self::SESSION_NAME, '', time() - 3600, '/', '', true, true);
    }

    private function deleteSessionFile(string $sessionId): void
    {
        $sessionFile = $this->getSessionFilePath($sessionId);
        if (file_exists($sessionFile)) {
            unlink($sessionFile);
        }
    }

    private function encrypt(string $data): string
    {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $this->encryptionKey, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    private function decrypt(string $data): string
    {
        $data = base64_decode($data);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $this->encryptionKey, 0, $iv);
    }
}