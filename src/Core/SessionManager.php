<?php

declare(strict_types=1);

namespace Src\Core;

use Swoole\Table;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Lock;

class SessionManager
{
    private Table $sessionTable;
    private Table $lockTable;
    private string $cookieName;
    private int $lifetime;
    private string $encryptionKey;

    public function __construct(int $tableSize = 1024, string $cookieName = 'PHPSESSID', int $lifetime = 3600, string $encryptionKey = '')
    {
        $this->sessionTable = new Table($tableSize);
        $this->sessionTable->column('data', Table::TYPE_STRING, 1024);
        $this->sessionTable->column('timestamp', Table::TYPE_INT);
        $this->sessionTable->create();

        $this->lockTable = new Table($tableSize);
        $this->lockTable->column('lock', Table::TYPE_INT, 8);
        $this->lockTable->create();

        $this->cookieName = $cookieName;
        $this->lifetime = $lifetime;
        $this->encryptionKey = $encryptionKey ?: bin2hex(random_bytes(32));
    }

    public function start(Request $request, Response $response): array
    {
        $sessionId = $this->getSessionId($request);
        $this->setSessionCookie($response, $sessionId);

        $this->lock($sessionId);
        $sessionData = $this->retrieveSession($sessionId);
        $this->unlock($sessionId);

        return $sessionData;
    }

    public function save(string $sessionId, array $data): void
    {
        $this->lock($sessionId);
        $encryptedData = $this->encrypt(json_encode($data));
        $this->sessionTable->set($sessionId, [
            'data' => $encryptedData,
            'timestamp' => time()
        ]);
        $this->unlock($sessionId);
    }

    public function destroy(string $sessionId, Response $response): void
    {
        $this->lock($sessionId);
        $this->sessionTable->del($sessionId);
        $this->unlock($sessionId);
        $this->removeSessionCookie($response);
    }

    public function gc(): void
    {
        $now = time();
        foreach ($this->sessionTable as $sessionId => $row) {
            if ($now - $row['timestamp'] > $this->lifetime) {
                $this->sessionTable->del($sessionId);
            }
        }
    }

    private function getSessionId(Request $request): string
    {
        $sessionId = $request->cookie[$this->cookieName] ?? '';
        if (empty($sessionId) || !$this->sessionTable->exist($sessionId)) {
            $sessionId = $this->generateSessionId();
        }
        return $sessionId;
    }

    private function retrieveSession(string $sessionId): array
    {
        if ($this->sessionTable->exist($sessionId)) {
            $encryptedData = $this->sessionTable->get($sessionId, 'data');
            $sessionData = json_decode($this->decrypt($encryptedData), true);
            $this->sessionTable->set($sessionId, ['timestamp' => time()]);
        } else {
            $sessionData = [];
            $this->save($sessionId, $sessionData);
        }
        return $sessionData;
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

    private function lock(string $sessionId): void
    {
        if (!$this->lockTable->exist($sessionId)) {
            $this->lockTable->set($sessionId, ['lock' => 0]);
        }
        $lock = new Lock(SWOOLE_MUTEX);
        $this->lockTable->set($sessionId, ['lock' => $lock->getLockId()]);
        $lock->lock();
    }

    private function unlock(string $sessionId): void
    {
        if ($this->lockTable->exist($sessionId)) {
            $lockId = $this->lockTable->get($sessionId, 'lock');
            if ($lockId) {
                $lock = new Lock(SWOOLE_MUTEX, $lockId);
                $lock->unlock();
            }
            $this->lockTable->del($sessionId);
        }
    }

    public function getCookieName(): string
    {
        return $this->cookieName;
    }
}