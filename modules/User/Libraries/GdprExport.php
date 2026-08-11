<?php

namespace User\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * GdprExport — collect every piece of personal data the CMS holds about a
 * single user and serialize it as JSON (full, structured) or CSV (flattened
 * key/value rows) for GDPR / KVKK data-access requests.
 *
 * Secrets (password hash, TOTP secret, API token hashes) are intentionally
 * excluded or redacted: the data-subject export is about personal data, not
 * credential material.
 */
class GdprExport
{
    protected BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    /**
     * Collect the full structured dataset for a user id or email.
     */
    public function collect(int $userId, ?string $email = null): array
    {
        $data = [
            'exported_at'     => date('c'),
            'subject'         => $this->profile($userId),
            'content'         => $this->content($userId),
            'comments'        => $this->comments($email),
            'media'           => $this->media($userId),
            'notifications'   => $this->notifications($userId),
            'activity_logs'   => $this->activityLogs($userId),
            'security_logs'   => $this->securityLogs($userId),
            'api_tokens'      => $this->apiTokens($userId),
            'magic_links'     => $this->magicLinks($userId),
            'password_resets' => $this->passwordResets($email),
            'login_attempts'  => $this->loginAttempts($email),
            'contact_submissions' => $this->contactSubmissions($email),
        ];

        return $data;
    }

    /**
     * Structured JSON payload (idempotent, pretty-printed).
     */
    public function toJson(int $userId, ?string $email = null): string
    {
        return json_encode(
            $this->collect($userId, $email),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ) ?: '{}';
    }

    /**
     * Flattened CSV: one row per (section, field, value) triple. Primitive
     * arrays and nested objects are JSON-encoded into the value cell.
     */
    public function toCsv(int $userId, ?string $email = null): string
    {
        return $this->flatten($this->collect($userId, $email));
    }

    /**
     * Flatten a nested dataset into a CSV-friendly one-row-per-value format.
     * Each value becomes `path,value` where list rows are keyed numerically
     * (e.g. `content.1.title`) so multi-row sections stay distinguishable.
     */
    protected function flatten(array $data, string $prefix = ''): string
    {
        $out = fopen('php://temp', 'r+');

        fputcsv($out, ['path', 'value']);

        $walk = static function (array $rows, string $path) use (&$walk, &$out): void {
            foreach ($rows as $key => $value) {
                $full = $path === '' ? (string) $key : $path . '.' . (string) $key;

                if (is_array($value)) {
                    if (! $value) {
                        fputcsv($out, [$full, '']);
                        continue;
                    }

                    if (array_is_list($value)) {
                        foreach ($value as $i => $item) {
                            if (is_array($item) && $item) {
                                $walk($item, $full . '.' . ($i + 1));
                            } else {
                                $encoded = is_scalar($item)
                                    ? (string) $item
                                    : (json_encode($item, JSON_UNESCAPED_UNICODE) ?: '');
                                fputcsv($out, [$full . '.' . ($i + 1), $encoded]);
                            }
                        }
                        continue;
                    }

                    $walk($value, $full);
                    continue;
                }

                if ($value === null) {
                    $value = '';
                } elseif (! is_scalar($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                }

                fputcsv($out, [$full, (string) $value]);
            }
        };

        $walk($data, $prefix);

        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return $csv ?: '';
    }

    /**
     * User profile with credential material stripped out.
     */
    public function profile(int $userId): ?array
    {
        $row = $this->db->table('users')->where('id', $userId)->get()->getRowArray();

        if (! $row) {
            return null;
        }

        unset($row['password_hash'], $row['totp_secret']);

        return $row;
    }

    public function content(int $userId): array
    {
        if (! $this->tableExists('content')) {
            return [];
        }

        return $this->db->table('content')
            ->where('author_id', $userId)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function comments(?string $email): array
    {
        if (! $email || ! $this->tableExists('comments')) {
            return [];
        }

        return $this->db->table('comments')
            ->where('author_email', $email)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function media(int $userId): array
    {
        if (! $this->tableExists('media')) {
            return [];
        }

        return $this->db->table('media')
            ->where('uploaded_by', $userId)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function notifications(int $userId): array
    {
        if (! $this->tableExists('notifications')) {
            return [];
        }

        return $this->db->table('notifications')
            ->where('recipient_id', $userId)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function activityLogs(int $userId): array
    {
        if (! $this->tableExists('activity_logs')) {
            return [];
        }

        return $this->db->table('activity_logs')
            ->where('user_id', $userId)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function securityLogs(int $userId): array
    {
        if (! $this->tableExists('security_logs')) {
            return [];
        }

        return $this->db->table('security_logs')
            ->where('user_id', $userId)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function apiTokens(int $userId): array
    {
        if (! $this->tableExists('api_tokens')) {
            return [];
        }

        $rows = $this->db->table('api_tokens')
            ->where('user_id', $userId)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($rows as &$row) {
            // Never ship raw credential material; keep only a fingerprint.
            if (isset($row['token_hash']) && $row['token_hash'] !== '') {
                $row['token_hash'] = 'redacted (' . substr((string) $row['token_hash'], 0, 8) . '…)';
            }
        }

        return $rows;
    }

    public function magicLinks(int $userId): array
    {
        if (! $this->tableExists('magic_links')) {
            return [];
        }

        $rows = $this->db->table('magic_links')
            ->where('user_id', $userId)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($rows as &$row) {
            if (isset($row['token']) && $row['token'] !== '') {
                $row['token'] = 'redacted';
            }
        }

        return $rows;
    }

    public function passwordResets(?string $email): array
    {
        if (! $email || ! $this->tableExists('password_resets')) {
            return [];
        }

        $rows = $this->db->table('password_resets')
            ->where('email', $email)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($rows as &$row) {
            if (isset($row['token']) && $row['token'] !== '') {
                $row['token'] = 'redacted';
            }
        }

        return $rows;
    }

    public function loginAttempts(?string $email): array
    {
        if (! $email || ! $this->tableExists('login_attempts')) {
            return [];
        }

        return $this->db->table('login_attempts')
            ->where('email', $email)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function contactSubmissions(?string $email): array
    {
        if (! $email || ! $this->tableExists('contact_submissions')) {
            return [];
        }

        $rows = [];
        foreach ($this->db->table('contact_submissions')->orderBy('id', 'ASC')->get()->getResultArray() as $row) {
            $payload = json_decode((string) ($row['data'] ?? '{}'), true) ?: [];

            if ($email !== '' && $this->matchesContactEmail($payload, $email)) {
                $row['match'] = 'email';
                $rows[] = $row;
            }
        }

        return $rows;
    }

    protected function matchesContactEmail(array $payload, string $email): bool
    {
        foreach ($payload as $value) {
            if (is_string($value) && strtolower(trim($value)) === strtolower(trim($email))) {
                return true;
            }
            if (is_array($value)) {
                foreach ($value as $nested) {
                    if (is_string($nested) && strtolower(trim($nested)) === strtolower(trim($email))) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    protected function tableExists(string $table): bool
    {
        return $this->db->tableExists($table);
    }
}