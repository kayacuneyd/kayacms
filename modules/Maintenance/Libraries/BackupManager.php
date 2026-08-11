<?php

namespace Maintenance\Libraries;

use Maintenance\Models\BackupModel;

/**
 * Database backup utilities: create, list, retain and delete SQLite snapshots.
 */
class BackupManager
{
    private BackupModel $model;
    private string $backupDir;

    public function __construct()
    {
        $this->model     = new BackupModel();
        $this->backupDir = WRITEPATH . 'backups';
    }

    /**
     * Path to the active SQLite database file for the current connection group.
     */
    public function databasePath(): string
    {
        $db = \Config\Database::connect();
        $path = $db->getDatabase();

        return $path;
    }

    /**
     * Create a backup of the current database snapshot.
     *
     * Uses SQLite's online VACUUM INTO to produce a consistent copy.
     */
    public function create(bool $includeUploads = false): array
    {
        if (! is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0775, true);
        }

        $stamp   = date('Y-m-d_Hi-s') . '-' . substr(bin2hex(random_bytes(4)), 0, 6);
        $dbPath  = $this->databasePath();

        try {
            $pdo = new \PDO('sqlite:' . $dbPath);
            $destination = $this->backupDir . DIRECTORY_SEPARATOR . "backup-{$stamp}.sqlite";

            // VACUUM INTO creates a hot standby copy (SQLite >= 3.27)
            $pdo->exec('PRAGMA busy_timeout = 10000');
            $pdo->exec("VACUUM INTO '" . str_replace("'", "''", $destination) . "'");

            $id = $this->model->insert([
                'filename' => basename($destination),
                'path'     => $destination,
                'size'     => filesize($destination),
                'type'     => 'db',
                'status'   => 'success',
                'message'  => 'Database backup created.',
            ]);

            return ['id' => (int) $id, 'filename' => basename($destination), 'path' => $destination];
        } catch (\Throwable $e) {
            $this->model->insert([
                'filename' => "backup-{$stamp}.sqlite",
                'path'     => $this->backupDir . DIRECTORY_SEPARATOR . "backup-{$stamp}.sqlite",
                'size'     => 0,
                'type'     => 'db',
                'status'   => 'error',
                'message'  => $e->getMessage(),
            ]);

            throw new \RuntimeException('Backup failed: ' . $e->getMessage());
        }
    }

    /**
     * List all backup records most recent first, with size formatting applied.
     */
    public function all(): array
    {
        $rows = $this->model->orderBy('created_at', 'DESC')->findAll();

        foreach ($rows as &$row) {
            $row['size_human'] = $this->humanSize((int) $row['size']);
        }

        return $rows;
    }

    public function get(int $id): ?array
    {
        return $this->model->find($id);
    }

    /**
     * Delete a backup file and its record.
     */
    public function delete(int $id): bool
    {
        $row = $this->model->find($id);

        if (! $row) {
            return false;
        }

        if ($row['path'] && is_file($row['path'])) {
            @unlink($row['path']);
        }

        return $this->model->delete($id) !== false;
    }

    /**
     * Prune old backup files beyond the given keep count.
     */
    public function prune(int $keep = 10): int
    {
        $rows = $this->model->orderBy('created_at', 'DESC')->findAll();
        $slack = array_slice($rows, $keep);
        $deleted = 0;

        foreach ($slack as $row) {
            if ($this->delete((int) $row['id'])) {
                $deleted++;
            }
        }

        return $deleted;
    }

    private function humanSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }
}