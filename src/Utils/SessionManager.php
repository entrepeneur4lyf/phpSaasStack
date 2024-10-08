<?php

declare(strict_types=1);

namespace Src\Utils;

use Swoole\Http\Request;
use Swoole\Http\Response;

class SessionManager
{
    private string $sessionName;
    private const SESSION_LIFETIME = 3600; // 1 hour
    private string $sessionPath;
    private string $encryptionKey;
    private string $hmacKey;

    public function __construct(string $sessionPath, string $encryptionKey)
    {
        $this->sessionPath = $sessionPath;
        $this->encryptionKey = $encryptionKey;
        $this->hmacKey = hash_hmac('sha256', $encryptionKey, $encryptionKey, true);
        $this->sessionName = $this->generateSessionName();
    }

    private function generateSessionName(): string
    {
        return 'sess_' . bin2hex(random_bytes(16)); // This generates a 32-character random string with a prefix
    }

    public function startSession(Request $request, Response $response): array
    {
        $sessionId = $this->getSessionIdFromCookie($request);

        if (!$sessionId || !$this->isValidSession($sessionId)) {
            $sessionId = $this->createSession();
        }

        $this->setSessionCookie($response, $sessionId);
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
        return bin2hex(random_bytes(32)); // 64-character session ID
    }

    private function isValidSession(string $sessionId): bool
    {
        $sessionFile = $this->getSessionFilePath($sessionId);
        return file_exists($sessionFile) &&
               (time() - filemtime($sessionFile) < self::SESSION_LIFETIME) &&
               $this->verifySessionIntegrity($sessionId);
    }

    private function loadSessionData(string $sessionId): array
    {
        $sessionFile = $this->getSessionFilePath($sessionId);
        if (file_exists($sessionFile)) {
            $encryptedData = file_get_contents($sessionFile);
            $decryptedData = $this->decrypt($encryptedData);
            $data = json_decode($decryptedData, true) ?? [];
            if ($this->verifySessionIntegrity($sessionId, $data)) {
                return $data;
            }
        }
        return [];
    }

    private function saveSessionData(string $sessionId, array $data): void
    {
        $sessionFile = $this->getSessionFilePath($sessionId);
        $encryptedData = $this->encrypt(json_encode($data));
        file_put_contents($sessionFile, $encryptedData);
        $this->updateSessionIntegrity($sessionId, $data);
    }

    private function regenerateSession(string $oldSessionId, Response $response): void
    {
        $newSessionId = $this->createSession();
        $oldSessionFile = $this->getSessionFilePath($oldSessionId);
        $newSessionFile = $this->getSessionFilePath($newSessionId);

        if (file_exists($oldSessionFile)) {
            rename($oldSessionFile, $newSessionFile);
            $this->updateSessionIntegrity($newSessionId, $this->loadSessionData($newSessionId));
        }

        $this->setSessionCookie($response, $newSessionId);
        $this->deleteSessionFile($oldSessionId);
    }

    private function getSessionFilePath(string $sessionId): string
    {
        return $this->sessionPath . '/' . $sessionId . '.sess';
    }

    private function getSessionIdFromCookie(Request $request): ?string
    {
        $sessionCookie = $request->cookie[$this->sessionName] ?? null;
        return $sessionCookie ? $this->decrypt($sessionCookie) : null;
    }

    private function setSessionCookie(Response $response, string $sessionId): void
    {
        $encryptedSessionId = $this->encrypt($sessionId);
        $response->cookie(
            $this->sessionName,
            $encryptedSessionId,
            time() + self::SESSION_LIFETIME,
            '/',
            '',
            true, // Secure flag
            true  // HttpOnly flag
        );
    }

    private function removeSessionCookie(Response $response): void
    {
        $response->cookie($this->sessionName, '', time() - 3600, '/', '', true, true);
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
        $encrypted = openssl_encrypt($data, 'AES-256-GCM', $this->encryptionKey, 0, $iv, $tag);
        return base64_encode($iv . $tag . $encrypted);
    }

    private function decrypt(string $data): string
    {
        $data = base64_decode($data);
        $iv = substr($data, 0, 16);
        $tag = substr($data, 16, 16);
        $encrypted = substr($data, 32);
        return openssl_decrypt($encrypted, 'AES-256-GCM', $this->encryptionKey, 0, $iv, $tag);
    }

    private function updateSessionIntegrity(string $sessionId, array $data): void
    {
        $hmac = hash_hmac('sha256', $sessionId . json_encode($data), $this->hmacKey, true);
        file_put_contents($this->getSessionFilePath($sessionId) . '.hmac', $hmac);
    }

    private function verifySessionIntegrity(string $sessionId, ?array $data = null): bool
    {
        $hmacFile = $this->getSessionFilePath($sessionId) . '.hmac';
        if (!file_exists($hmacFile)) {
            return false;
        }
        $storedHmac = file_get_contents($hmacFile);
        $data = $data ?? $this->loadSessionData($sessionId);
        $calculatedHmac = hash_hmac('sha256', $sessionId . json_encode($data), $this->hmacKey, true);
        return hash_equals($storedHmac, $calculatedHmac);
    }

    public function getSessionName(): string
    {
        return $this->sessionName;
    }
}
