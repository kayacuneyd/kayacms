<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use Config\Database;

class SeoAuditController extends BaseAdminController
{
    public function index(): string
    {
        $db = Database::connect();
        $items = $this->contentIssues($db);

        return $this->render('admin/seo_audit', [
            'active' => 'seo_audit',
            'title' => 'SEO Audit',
            'items' => $items,
            'summary' => $this->summary($items),
            'endpoints' => $this->endpointChecks(),
        ]);
    }

    protected function contentIssues($db): array
    {
        try {
            $rows = $db->table('content')
                ->select('id,title,slug,content_type,status,locale,excerpt,body,meta_title,meta_description,featured_image,canonical_url,published_at,updated_at,custom_data')
                ->where('deleted_at', null)
                ->whereIn('content_type', ['article', 'page', 'podcast'])
                ->orderBy('updated_at', 'DESC')
                ->limit(150)
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            return [];
        }

        $items = [];
        foreach ($rows as $row) {
            $issues = [];
            $score = 100;
            $title = trim((string) ($row['title'] ?? ''));
            $slug = trim((string) ($row['slug'] ?? ''));
            $metaTitle = trim((string) ($row['meta_title'] ?? ''));
            $metaDesc = trim((string) ($row['meta_description'] ?? ''));
            $excerpt = trim((string) ($row['excerpt'] ?? ''));
            $bodyText = trim(strip_tags((string) ($row['body'] ?? '')));
            $type = (string) ($row['content_type'] ?? 'article');

            $add = static function (string $label, string $severity, string $action, int $penalty) use (&$issues, &$score): void {
                $issues[] = ['label' => $label, 'severity' => $severity, 'action' => $action];
                $score -= $penalty;
            };

            if ($title === '') { $add('Missing title', 'high', 'Add a clear editorial title.', 25); }
            elseif (mb_strlen($title) > 110) { $add('Title too long', 'medium', 'Shorten the title below 110 characters.', 10); }

            if ($slug === '') { $add('Missing slug', 'high', 'Generate a readable slug.', 20); }
            elseif (! preg_match('/^[a-z0-9\-]+$/', $slug)) { $add('Slug format', 'medium', 'Use lowercase letters, numbers and hyphens only.', 8); }

            if ($metaTitle !== '' && mb_strlen($metaTitle) > 70) { $add('Meta title too long', 'medium', 'Keep meta title near 50–65 characters.', 8); }
            if ($metaDesc === '') { $add('Missing meta description', 'high', 'Write a search/snippet description.', 20); }
            elseif (mb_strlen($metaDesc) < 80 || mb_strlen($metaDesc) > 170) { $add('Meta description length', 'medium', 'Target 120–155 characters.', 10); }

            if ($excerpt === '' && $type !== 'page') { $add('Missing excerpt', 'medium', 'Add a short card/deck summary.', 8); }
            if (trim((string) ($row['featured_image'] ?? '')) === '' && $type !== 'page') { $add('Missing featured image', 'high', 'Choose an editorial image for cards and Open Graph.', 18); }
            if ($type === 'article' && str_word_count($bodyText) < 180) { $add('Thin body copy', 'medium', 'Expand the piece or mark it as a short news item intentionally.', 8); }
            if ($type === 'podcast') {
                $custom = json_decode((string) ($row['custom_data'] ?? ''), true) ?: [];
                if (empty($custom['audio_url']) && empty($custom['download_url'])) { $add('Missing podcast audio', 'high', 'Add audio_url or download_url in custom data.', 25); }
                if (empty($custom['episode_number'])) { $add('Missing episode number', 'low', 'Add episode_number for sorting and RSS display.', 4); }
                if (empty($custom['duration'])) { $add('Missing duration', 'low', 'Add duration for listener expectations.', 4); }
            }
            if (($row['status'] ?? '') === 'published' && empty($row['published_at'])) { $add('Missing publish date', 'medium', 'Set a publish date for feeds and schema.', 8); }

            if ($issues) {
                $row['issues'] = $issues;
                $row['score'] = max(0, $score);
                $row['edit_url'] = site_url('admin/content/edit/' . (int) $row['id']);
                $row['public_url'] = $type === 'page' ? site_url($slug) : site_url('content/' . $slug);
                $items[] = $row;
            }
        }
        return $items;
    }

    protected function summary(array $items): array
    {
        $counts = [];
        foreach ($items as $item) {
            foreach ($item['issues'] as $issue) {
                $label = is_array($issue) ? ($issue['label'] ?? 'Issue') : (string) $issue;
                $counts[$label] = ($counts[$label] ?? 0) + 1;
            }
        }
        arsort($counts);
        return $counts;
    }

    protected function endpointChecks(): array
    {
        return [
            ['label' => 'Sitemap', 'url' => site_url('sitemap.xml'), 'hint' => 'Search index inventory'],
            ['label' => 'RSS', 'url' => site_url('feed.xml'), 'hint' => 'Article feed'],
            ['label' => 'Robots', 'url' => site_url('robots.txt'), 'hint' => 'Crawler policy'],
        ];
    }
}
