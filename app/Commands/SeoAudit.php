<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Content\Models\ContentModel;

class SeoAudit extends BaseCommand
{
    protected $group = 'SEO';
    protected $name = 'seo:audit';
    protected $description = 'Write SEO and legacy redirect quality reports for published content.';

    public function run(array $params)
    {
        $dir = $this->path((string) (CLI::getOption('dir') ?: WRITEPATH . 'reports'));
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $content = (new ContentModel())
            ->where('status', 'published')
            ->where('deleted_at', null)
            ->orderBy('id', 'ASC')
            ->findAll();

        $descriptions = [];
        foreach ($content as $item) {
            $desc = trim((string) ($item->meta_description ?? ''));
            if ($desc !== '') {
                $descriptions[$desc] = ($descriptions[$desc] ?? 0) + 1;
            }
        }

        $seoPath = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . 'seo-audit.csv';
        $redirectPath = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . 'redirect-audit.csv';
        $seoHandle = fopen($seoPath, 'wb');
        $redirectHandle = fopen($redirectPath, 'wb');

        fputcsv($seoHandle, ['id', 'type', 'slug', 'title', 'issue', 'meta_title_len', 'meta_description_len', 'canonical_url', 'source_url']);
        fputcsv($redirectHandle, ['id', 'slug', 'source_url', 'legacy_path', 'target_url', 'status']);

        $issueCount = 0;
        $redirectCount = 0;
        foreach ($content as $item) {
            $issues = $this->issues($item, $descriptions);
            foreach ($issues as $issue) {
                $issueCount++;
                fputcsv($seoHandle, [
                    $item->id,
                    $item->content_type,
                    $item->slug,
                    $item->title,
                    $issue,
                    mb_strlen((string) ($item->meta_title ?? '')),
                    mb_strlen((string) ($item->meta_description ?? '')),
                    $item->canonical_url ?? '',
                    $item->source_url ?? '',
                ]);
            }

            if (! empty($item->source_url)) {
                $legacyPath = parse_url((string) $item->source_url, PHP_URL_PATH) ?: '';
                $target = '/content/' . $item->slug;
                fputcsv($redirectHandle, [
                    $item->id,
                    $item->slug,
                    $item->source_url,
                    $legacyPath,
                    $target,
                    $legacyPath ? 'mapped' : 'missing_legacy_path',
                ]);
                $redirectCount++;
            }
        }

        fclose($seoHandle);
        fclose($redirectHandle);

        CLI::write("SEO audit complete: {$issueCount} issues.", $issueCount ? 'yellow' : 'green');
        CLI::write("Redirect mappings: {$redirectCount}.", 'green');
        CLI::write("SEO report: {$seoPath}");
        CLI::write("Redirect report: {$redirectPath}");
    }

    private function path(string $path): string
    {
        if (preg_match('/^[A-Za-z]:[\/\\\\]/', $path) || str_starts_with($path, '/')) {
            return $path;
        }
        return ROOTPATH . ltrim($path, '/\\');
    }

    private function issues($item, array $descriptions): array
    {
        $issues = [];
        $metaTitle = trim((string) ($item->meta_title ?? ''));
        $metaDescription = trim((string) ($item->meta_description ?? ''));

        if ($metaTitle === '') {
            $issues[] = 'missing_meta_title';
        } elseif (mb_strlen($metaTitle) > 70) {
            $issues[] = 'long_meta_title';
        }

        if ($metaDescription === '') {
            $issues[] = 'missing_meta_description';
        } elseif (mb_strlen($metaDescription) < 80) {
            $issues[] = 'short_meta_description';
        } elseif (mb_strlen($metaDescription) > 170) {
            $issues[] = 'long_meta_description';
        }

        if ($metaDescription !== '' && ($descriptions[$metaDescription] ?? 0) > 1) {
            $issues[] = 'duplicate_meta_description';
        }

        if (empty($item->canonical_url)) {
            $issues[] = 'missing_canonical_url';
        }

        return $issues;
    }
}
