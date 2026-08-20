<?php
namespace App\Models;

use CodeIgniter\Model;

class ContentMetricModel extends Model
{
    protected $table = 'content_metrics';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $allowedFields = [
        'content_id',
        'views_total',
        'views_imported',
        'views_internal',
        'avg_read_seconds',
        'last_viewed_at',
        'source_snapshot',
    ];
    protected $useTimestamps = true;

    public function incrementInternal(int $contentId, int $readSeconds = 0): void
    {
        $row = $this->where('content_id', $contentId)->first();
        $now = date('Y-m-d H:i:s');

        if (! $row) {
            $this->insert([
                'content_id' => $contentId,
                'views_total' => 1,
                'views_imported' => 0,
                'views_internal' => 1,
                'avg_read_seconds' => max(0, $readSeconds),
                'last_viewed_at' => $now,
            ]);
            return;
        }

        $viewsInternal = (int) $row['views_internal'] + 1;
        $avg = (int) ($row['avg_read_seconds'] ?? 0);
        if ($readSeconds > 0) {
            $avg = $avg > 0 ? (int) round(($avg + $readSeconds) / 2) : $readSeconds;
        }

        $this->update((int) $row['id'], [
            'views_total' => (int) $row['views_total'] + 1,
            'views_internal' => $viewsInternal,
            'avg_read_seconds' => $avg,
            'last_viewed_at' => $now,
        ]);
    }

    public function upsertImported(int $contentId, int $views, array $snapshot = []): void
    {
        $row = $this->where('content_id', $contentId)->first();
        $payload = [
            'content_id' => $contentId,
            'views_imported' => max(0, $views),
            'source_snapshot' => $snapshot ? json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
        ];

        if (! $row) {
            $payload['views_total'] = max(0, $views);
            $this->insert($payload);
            return;
        }

        $payload['views_total'] = (int) $views + (int) ($row['views_internal'] ?? 0);
        $this->update((int) $row['id'], $payload);
    }
}
