<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Content\Models\ContentModel;
use Setting\Models\SettingModel;

class SeoFix extends BaseCommand
{
    protected $group = 'SEO';
    protected $name = 'seo:fix';
    protected $description = 'Apply safe SEO fixes: canonical URLs and fallback metadata only.';

    public function run(array $params)
    {
        $apply = (bool) CLI::getOption('apply');
        $siteUrl = rtrim((string) (CLI::getOption('site-url') ?: (new SettingModel())->getSetting('site_url', 'http://localhost:8080')), '/');
        $model = new ContentModel();
        $rows = $model->where('status', 'published')->findAll();
        $updates = 0;

        foreach ($rows as $item) {
            $payload = [];
            if (empty($item->canonical_url)) {
                $prefix = $item->content_type === 'page' ? '/page/' : '/content/';
                $payload['canonical_url'] = $siteUrl . $prefix . $item->slug;
            }
            if (empty($item->meta_title)) {
                $payload['meta_title'] = $item->title;
            }
            if (empty($item->meta_description)) {
                $excerpt = trim(strip_tags((string) ($item->excerpt ?: $item->body)));
                if ($excerpt !== '') {
                    $payload['meta_description'] = mb_substr(preg_replace('/\s+/', ' ', $excerpt), 0, 160);
                }
            }

            if ($payload) {
                $updates++;
                if ($apply) {
                    $model->update((int) $item->id, $payload);
                }
            }
        }

        CLI::write(($apply ? 'Applied' : 'Would apply') . " safe SEO updates to {$updates} rows.", $updates ? 'yellow' : 'green');
        if (! $apply) {
            CLI::write('Dry run only. Re-run with --apply to write changes.');
        }
    }
}
