<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use Config\Database;
use Content\Models\CommentModel;
use Content\Models\ContentModel;
use Media\Models\MediaJobModel;
use Newsletter\Models\SubscriberModel;
use User\Models\SecurityLogModel;

class SystemHealthController extends BaseAdminController
{
    public function index(): string
    {
        $db = Database::connect();
        $checks = [];

        $checks[] = $this->check('PHP 8.2+', version_compare(PHP_VERSION, '8.2.0', '>='), 'Current: ' . PHP_VERSION);
        $checks[] = $this->check('Database connection', $this->canQueryDatabase($db), 'Primary CMS database can be queried.');
        $checks[] = $this->check('Writable directory', is_writable(WRITEPATH), WRITEPATH);
        $checks[] = $this->check('Page cache directory', is_dir(WRITEPATH . 'page_cache') && is_writable(WRITEPATH . 'page_cache'), WRITEPATH . 'page_cache');
        $checks[] = $this->check('Runtime cache directory', is_dir(WRITEPATH . 'cache') && is_writable(WRITEPATH . 'cache'), WRITEPATH . 'cache');
        $checks[] = $this->check('Uploads directory', is_dir(FCPATH . 'assets/uploads') && is_writable(FCPATH . 'assets/uploads'), FCPATH . 'assets/uploads');
        $checks[] = $this->check('Environment file protected', is_file(ROOTPATH . '.env') && ! is_file(FCPATH . '.env'), '.env exists outside public web root.');
        $checks[] = $this->check('Public routes', $this->publicSmoke(), 'Home, feed and sitemap routes are routable.');

        $tables = ['content', 'users', 'comments', 'media', 'newsletter_subscribers', 'settings'];
        foreach ($tables as $table) {
            $checks[] = $this->check('Table: ' . $table, $this->tableExists($db, $table), $table);
        }

        $data = [
            'active' => 'system_health',
            'title' => 'System Health',
            'checks' => $checks,
            'summary' => $this->summary($checks),
            'metrics' => $this->metrics(),
            'logs' => $this->recentLogLines(),
        ];

        return $this->render('admin/system_health', $data);
    }

    protected function check(string $label, bool $ok, string $detail = ''): array
    {
        return ['label' => $label, 'ok' => $ok, 'detail' => $detail];
    }

    protected function summary(array $checks): array
    {
        $failed = array_values(array_filter($checks, static fn ($check) => ! $check['ok']));
        return ['total' => count($checks), 'failed' => count($failed), 'ok' => count($checks) - count($failed)];
    }

    protected function canQueryDatabase($db): bool
    {
        try {
            return (bool) $db->query('SELECT 1')->getRowArray();
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function tableExists($db, string $table): bool
    {
        try {
            return $db->tableExists($table);
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function publicSmoke(): bool
    {
        foreach (['/', '/feed.xml', '/sitemap.xml'] as $path) {
            if (! is_string(site_url($path)) || site_url($path) === '') {
                return false;
            }
        }
        return true;
    }

    protected function metrics(): array
    {
        $metrics = [];
        $content = new ContentModel();
        $comments = new CommentModel();
        $subscribers = new SubscriberModel();

        $metrics[] = ['label' => 'Published articles', 'value' => $this->safeCount(fn () => $content->where('content_type', 'article')->where('status', 'published')->countAllResults())];
        $metrics[] = ['label' => 'Draft content', 'value' => $this->safeCount(fn () => (new ContentModel())->where('status', 'draft')->countAllResults())];
        $metrics[] = ['label' => 'Pending comments', 'value' => $this->safeCount(fn () => $comments->where('status', 'pending')->countAllResults())];
        $metrics[] = ['label' => 'Newsletter subscribers', 'value' => $this->safeCount(fn () => $subscribers->where('status', 'subscribed')->countAllResults())];
        $metrics[] = ['label' => 'Failed media jobs', 'value' => $this->safeCount(fn () => (new MediaJobModel())->where('status', 'failed')->countAllResults())];
        $metrics[] = ['label' => 'Security log entries', 'value' => $this->safeCount(fn () => (new SecurityLogModel())->countAllResults())];
        $metrics[] = ['label' => 'Disk free', 'value' => $this->formatBytes((int) @disk_free_space(ROOTPATH))];
        $metrics[] = ['label' => 'Disk total', 'value' => $this->formatBytes((int) @disk_total_space(ROOTPATH))];

        return $metrics;
    }

    protected function safeCount(callable $callback)
    {
        try {
            return $callback();
        } catch (\Throwable $e) {
            return '—';
        }
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '—';
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 1) . ' ' . $units[$i];
    }

    protected function recentLogLines(): array
    {
        $files = glob(WRITEPATH . 'logs/log-*.php') ?: [];
        rsort($files);
        $file = $files[0] ?? null;
        if (! $file || ! is_readable($file)) {
            return [];
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        $lines = array_values(array_filter($lines, static fn ($line) => ! str_starts_with($line, '<?php') && ! str_starts_with($line, 'defined(')));
        return array_slice($lines, -12);
    }
}
