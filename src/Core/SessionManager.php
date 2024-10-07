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

    public function __construct(SwooleRedisCache $cache, string $cookieName = 'PHPSESSID', int $lifetime = 3600, string $encryptionKey = '')
    {
        $this->cache = $cache;
        $this->cookieName = $cookieName;
        $this->lifetime = $lifetime;
        $this->encryptionKey = $encryptionKey ?: bin2hex(random_bytes(32));
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
    }

    public function destroy(string $sessionId, Response $response): void
    {
        $this->cache->delete("session:{$sessionId}");
        $this->removeSessionCookie($response);
    }

    public function gc(): void
    {
        // Redis automatically expires keys, so we don't need to implement garbage collection
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
            return json_decode($this->decrypt($encryptedData), true) ?? [];
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
        return bin2hex(random_bytes(16));
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

    public function getCookieName(): string
    {
        return $this->cookieName;
    }
}