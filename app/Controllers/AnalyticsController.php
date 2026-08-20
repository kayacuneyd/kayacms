<?php
namespace App\Controllers;

use App\Models\ContentEventModel;
use App\Models\ContentMetricModel;
use App\Models\DailyContentMetricModel;
use CodeIgniter\HTTP\ResponseInterface;

class AnalyticsController extends BaseController
{
    public function collect(): ResponseInterface
    {
        if (! $this->request->is('post')) {
            return $this->response->setStatusCode(405)->setJSON(['ok' => false]);
        }

        try {
            $payload = $this->request->getJSON(true);
        } catch (\Throwable) {
            $payload = null;
        }
        if (! is_array($payload)) {
            $payload = json_decode((string) $this->request->getBody(), true);
        }
        if (! is_array($payload)) {
            $payload = $this->request->getPost();
        }
        $vars = $this->request->getVar();
        if (is_array($vars)) {
            $payload = array_merge($vars, is_array($payload) ? $payload : []);
        }
        $query = $this->request->getGet();
        if (is_array($query)) {
            $payload = array_merge($query, is_array($payload) ? $payload : []);
        }
        $slug = trim((string) ($payload['slug'] ?? ''), '/');
        $url = substr((string) ($payload['url'] ?? current_url()), 0, 700);
        $readSeconds = max(0, min(3600, (int) ($payload['read_seconds'] ?? 0)));

        if ($slug === '') {
            $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
            if (str_starts_with($path, 'content/')) {
                $slug = substr($path, strlen('content/'));
            }
        }

        if ($slug === '') {
            return $this->response->setStatusCode(204);
        }

        $content = \Config\Database::connect()
            ->table('content')
            ->select('id, slug')
            ->where('slug', $slug)
            ->where('deleted_at IS NULL', null, false)
            ->get()
            ->getRowArray();
        if (! $content) {
            return $this->response->setStatusCode(204);
        }

        $contentId = (int) $content['id'];
        (new ContentMetricModel())->incrementInternal($contentId, $readSeconds);
        (new DailyContentMetricModel())->increment($contentId);

        (new ContentEventModel())->insert([
            'content_id' => $contentId,
            'event_name' => 'page_view',
            'url' => $url,
            'referrer' => substr((string) ($payload['referrer'] ?? $this->request->getServer('HTTP_REFERER')), 0, 700),
            'user_agent_hash' => hash('sha256', (string) $this->request->getUserAgent()),
            'device_type' => $this->deviceType((string) $this->request->getUserAgent()),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setStatusCode(204);
    }

    private function deviceType(string $ua): string
    {
        $ua = strtolower($ua);
        if (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) {
            return 'tablet';
        }
        if (str_contains($ua, 'mobile') || str_contains($ua, 'android') || str_contains($ua, 'iphone')) {
            return 'mobile';
        }
        return 'desktop';
    }
}
