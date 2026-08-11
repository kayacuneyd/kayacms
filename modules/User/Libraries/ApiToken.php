<?php

namespace User\Libraries;

/**
 * Stateless personal access token management.
 * Only the SHA-256 hash of a token is stored; the plaintext is shown once.
 */
class ApiToken
{
    protected $table = 'api_tokens';

    /**
     * Generate a new opaque token (never stored in plaintext).
     * Returns both the plaintext and its hash.
     *
     * @return array{plain: string, token: string}
     */
    public function create(int $userId, string $name, array $scopes = [], ?int $expiresInSeconds = null): array
    {
        $plain = bin2hex(random_bytes(24));

        $data = [
            'user_id'    => $userId,
            'name'       => $name,
            'token_hash' => hash('sha256', $plain),
            'scopes'     => json_encode($scopes),
            'expires_at' => $expiresInSeconds ? date('Y-m-d H:i:s', time() + $expiresInSeconds) : null,
        ];

        $db = \Config\Database::connect();
        $db->table($this->table)->insert($data);

        return [
            'plain' => $plain,
            'token' => $data['token_hash'],
            'id'    => (int) $db->insertID(),
        ];
    }

    /**
     * Resolve a plaintext token back to a usable record (or null).
     */
    public function resolve(?string $plain): ?array
    {
        if (! $plain) {
            return null;
        }

        $row = \Config\Database::connect()
            ->table($this->table)
            ->where('token_hash', hash('sha256', $plain))
            ->get()
            ->getRowArray();

        if (! $row) {
            return null;
        }

        if ($row['expires_at'] && strtotime($row['expires_at']) < time()) {
            return null;
        }

        return $row;
    }

    /**
     * Touch last_used_at.
     */
    public function touch(int $id): void
    {
        \Config\Database::connect()
            ->table($this->table)
            ->where('id', $id)
            ->update(['last_used_at' => date('Y-m-d H:i:s')]);
    }

    public function forUser(int $userId): array
    {
        return \Config\Database::connect()
            ->table($this->table)
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function revoke(int $id): bool
    {
        return \Config\Database::connect()
            ->table($this->table)
            ->where('id', $id)
            ->delete() !== false;
    }
}