<?php

declare(strict_types=1);

namespace Src\Core;

use Src\Cache\SwooleRedisCache;
use Swoole\Http\Request;
use Swoole\Http\Response;

class SessionManager
{
    private SwooleRedisCache $cache;
    private string $cookieName;
    private int $lifetime;
    private string $encryptionKey;
    private string $hmacKey;

    public function __construct(SwooleRedisCache $cache, string $cookieName = 'PHPSESSID', int $lifetime = 3600, string $encryptionKey = '')
    {
        $this->cache = $cache;
        $this->cookieName = $cookieName;
        $this->lifetime = $lifetime;
        $this->encryptionKey = $encryptionKey ?: bin2hex(random_bytes(32));
        $this->hmacKey = hash_hmac('sha256', $this->encryptionKey, $this->encryptionKey, true);
    }

    public function start(Request $request, Response $response): array
    {
        $sessionId = $this->getSessionId($request);
        $this->setSessionCookie($response, $sessionId);

        return $this->retrieveSession($sessionId);
    }

    public function save(string $sessionId, array $data): void
    {
        $encryptedData = $this->encrypt(json_encode($data));
        $this->cache->set("session:{$sessionId}", $encryptedData, $this->lifetime);
        $this->updateSessionIntegrity($sessionId, $data);
    }

    public function destroy(string $sessionId, Response $response): void
    {
        $this->cache->delete("session:{$sessionId}");
        $this->cache->delete("session:{$sessionId}:hmac");
        $this->removeSessionCookie($response);
    }

    private function getSessionId(Request $request): string
    {
        $sessionId = $request->cookie[$this->cookieName] ?? '';
        if (empty($sessionId) || !$this->cache->has("session:{$sessionId}")) {
            $sessionId = $this->generateSessionId();
        }
        return $sessionId;
    }

    private function retrieveSession(string $sessionId): array
    {
        $encryptedData = $this->cache->get("session:{$sessionId}");
        if ($encryptedData !== null) {
            $data = json_decode($this->decrypt($encryptedData), true) ?? [];
            if ($this->verifySessionIntegrity($sessionId, $data)) {
                return $data;
            }
        }
        return [];
    }

    private function setSessionCookie(Response $response, string $sessionId): void
    {
        $response->cookie(
            $this->cookieName,
            $sessionId,
            time() + $this->lifetime,
            '/',
            '',
            true,
            true
        );
    }

    private function removeSessionCookie(Response $response): void
    {
        $response->cookie(
            $this->cookieName,
            '',
            time() - 3600,
            '/',
            '',
            true,
            true
        );
    }

    private function generateSessionId(): string
    {
        return bin2hex(random_bytes(32));
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
        $this->cache->set("session:{$sessionId}:hmac", $hmac, $this->lifetime);
    }

    private function verifySessionIntegrity(string $sessionId, array $data): bool
    {
        $storedHmac = $this->cache->get("session:{$sessionId}:hmac");
        if ($storedHmac === null) {
            return false;
        }
        $calculatedHmac = hash_hmac('sha256', $sessionId . json_encode($data), $this->hmacKey, true);
        return hash_equals($storedHmac, $calculatedHmac);
    }

    public function getCookieName(): string
    {
        return $this->cookieName;
    }
}
