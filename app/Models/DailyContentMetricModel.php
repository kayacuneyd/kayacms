<?php
namespace App\Models;

use CodeIgniter\Model;

class DailyContentMetricModel extends Model
{
    protected $table = 'daily_content_metrics';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $allowedFields = ['content_id', 'metric_date', 'views', 'source'];
    protected $useTimestamps = true;

    public function increment(int $contentId, string $source = 'internal', ?string $date = null): void
    {
        $date = $date ?: date('Y-m-d');
        $row = $this->where('content_id', $contentId)
            ->where('metric_date', $date)
            ->where('source', $source)
            ->first();

        if (! $row) {
            $this->insert([
                'content_id' => $contentId,
                'metric_date' => $date,
                'views' => 1,
                'source' => $source,
            ]);
            return;
        }

        $this->update((int) $row['id'], ['views' => (int) $row['views'] + 1]);
    }
}
