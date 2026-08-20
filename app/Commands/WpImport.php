<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Content\Models\ContentModel;
use Media\Models\MediaModel;
use Taxonomy\Models\TermModel;
use Taxonomy\Models\TermRelationshipModel;

class WpImport extends BaseCommand
{
    protected $group = 'Import';
    protected $name = 'wp:import';
    protected $description = 'Import WordPress WXR export into KayaCMS content, taxonomy and media tables.';
    protected $usage = 'wp:import path/to/export.xml [--author 1] [--locale tr] [--download-media]';

    public function run(array $params)
    {
        $path = $params[0] ?? null;
        if (! $path || ! is_file($path)) {
            CLI::error('A readable WordPress export XML path is required.');
            return;
        }

        $xml = simplexml_load_file($path, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (! $xml || ! isset($xml->channel)) {
            CLI::error('The file does not look like a WordPress WXR export.');
            return;
        }

        $authorId = (int) (CLI::getOption('author') ?: 1);
        $locale = (string) (CLI::getOption('locale') ?: 'tr');
        $downloadMedia = (bool) CLI::getOption('download-media');

        $termMap = $this->importTerms($xml, $locale);
        $mediaMap = $this->importAttachments($xml, $authorId, $downloadMedia);
        $stats = $this->importContent($xml, $authorId, $locale, $termMap, $mediaMap);

        CLI::write("Imported {$stats['content']} content rows, {$stats['terms']} relationships, {$stats['media']} media references.", 'green');
    }

    protected function importTerms(\SimpleXMLElement $xml, string $locale): array
    {
        $model = new TermModel();
        $map = [];

        foreach ($xml->channel->children('wp', true)->category as $category) {
            $slug = (string) $category->category_nicename;
            $name = (string) $category->cat_name;
            $map['category:' . $slug] = $this->ensureTerm($model, $name, $slug, 'category', $locale);
        }

        foreach ($xml->channel->children('wp', true)->tag as $tag) {
            $slug = (string) $tag->tag_slug;
            $name = (string) $tag->tag_name;
            $map['post_tag:' . $slug] = $this->ensureTerm($model, $name, $slug, 'tag', $locale);
        }

        return $map;
    }

    protected function ensureTerm(TermModel $model, string $name, string $slug, string $type, string $locale): int
    {
        $slug = $this->slug($slug ?: $name);
        $existing = $model->where('slug', $slug)->where('locale', $locale)->first();
        if ($existing) {
            return (int) $existing['id'];
        }

        return (int) $model->insert([
            'name' => $name ?: $slug,
            'slug' => $slug,
            'taxonomy_type' => $type,
            'locale' => $locale,
            'translation_group_id' => bin2hex(random_bytes(8)),
            'description' => '',
        ]);
    }

    protected function importAttachments(\SimpleXMLElement $xml, int $authorId, bool $downloadMedia): array
    {
        $map = [];
        $media = new MediaModel();
        $count = 0;

        foreach ($xml->channel->item as $item) {
            $wp = $item->children('wp', true);
            if ((string) $wp->post_type !== 'attachment') {
                continue;
            }

            $wpId = (string) $wp->post_id;
            $url = trim((string) $wp->attachment_url);
            if ($url === '') {
                continue;
            }

            $path = $downloadMedia ? $this->downloadMedia($url) : $url;
            $map[$wpId] = $path;

            $existing = $media->where('path', $path)->first();
            if (! $existing) {
                $filename = basename(parse_url($path, PHP_URL_PATH) ?: $path);
                $media->insert([
                    'filename' => $filename,
                    'original_name' => $filename,
                    'mime_type' => $this->mimeFromFilename($filename),
                    'size' => 0,
                    'alt_text' => (string) $item->title,
                    'path' => $path,
                    'file_path' => $path,
                    'uploaded_by' => $authorId,
                ]);
                $count++;
            }
        }

        $map['_count'] = $count;
        return $map;
    }

    protected function importContent(\SimpleXMLElement $xml, int $authorId, string $locale, array $termMap, array $mediaMap): array
    {
        $content = new ContentModel();
        $relations = new TermRelationshipModel();
        $stats = ['content' => 0, 'terms' => 0, 'media' => (int) ($mediaMap['_count'] ?? 0)];

        foreach ($xml->channel->item as $item) {
            $wp = $item->children('wp', true);
            $postType = (string) $wp->post_type;
            if (! in_array($postType, ['post', 'page'], true)) {
                continue;
            }

            $slug = $this->uniqueContentSlug($content, $this->slug((string) $wp->post_name ?: (string) $item->title));
            $status = ((string) $wp->status) === 'publish' ? 'published' : 'draft';
            $publishedAt = $this->dateOrNull((string) $wp->post_date);
            $encoded = $item->children('content', true);
            $excerpt = $item->children('excerpt', true);
            $thumbnailId = $this->postMeta($wp, '_thumbnail_id');

            $id = $content->insert([
                'locale' => $locale,
                'translation_group_id' => bin2hex(random_bytes(8)),
                'source_system' => 'wordpress',
                'source_id' => (string) $wp->post_id,
                'source_url' => (string) $item->link,
                'content_type' => $postType === 'page' ? 'page' : 'article',
                'title' => (string) $item->title,
                'slug' => $slug,
                'body' => (string) $encoded->encoded,
                'excerpt' => (string) $excerpt->encoded ?: null,
                'status' => $status,
                'author_id' => $authorId,
                'featured_image' => $thumbnailId && isset($mediaMap[$thumbnailId]) ? $mediaMap[$thumbnailId] : null,
                'meta_title' => (string) $item->title,
                'meta_description' => strip_tags((string) $excerpt->encoded) ?: null,
                'published_at' => $publishedAt,
                'created_at' => $publishedAt,
                'updated_at' => $this->dateOrNull((string) $wp->post_modified) ?: $publishedAt,
            ]);

            if (! $id) {
                CLI::write('Skipped "' . (string) $item->title . '": ' . implode(', ', $content->errors()), 'yellow');
                continue;
            }

            $termIds = [];
            foreach ($item->category as $cat) {
                $domain = (string) $cat['domain'];
                $nicename = (string) $cat['nicename'];
                $key = $domain . ':' . $nicename;
                if (isset($termMap[$key])) {
                    $termIds[] = $termMap[$key];
                } elseif (in_array($domain, ['category', 'post_tag'], true)) {
                    $termIds[] = $this->ensureTerm(new TermModel(), (string) $cat, $nicename, $domain === 'post_tag' ? 'tag' : 'category', $locale);
                }
            }

            if ($termIds) {
                $relations->attachTerms((int) $id, array_values(array_unique($termIds)));
                $stats['terms'] += count(array_unique($termIds));
            }

            $stats['content']++;
        }

        return $stats;
    }

    protected function postMeta(\SimpleXMLElement $wp, string $key): ?string
    {
        foreach ($wp->postmeta as $meta) {
            if ((string) $meta->meta_key === $key) {
                return (string) $meta->meta_value;
            }
        }

        return null;
    }

    protected function uniqueContentSlug(ContentModel $model, string $slug): string
    {
        $base = $slug ?: 'imported-content';
        $candidate = $base;
        $i = 2;
        while ($model->where('slug', $candidate)->first()) {
            $candidate = $base . '-' . $i;
            $i++;
        }

        return $candidate;
    }

    protected function downloadMedia(string $url): string
    {
        $dir = FCPATH . 'assets/uploads/imported';
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = basename(parse_url($url, PHP_URL_PATH) ?: 'media');
        $filename = preg_replace('/[^A-Za-z0-9._-]/', '-', $filename) ?: ('media-' . time());
        $target = $dir . '/' . $filename;

        if (! is_file($target)) {
            $data = @file_get_contents($url);
            if ($data !== false) {
                file_put_contents($target, $data);
            }
        }

        return '/assets/uploads/imported/' . $filename;
    }

    protected function slug(string $value): string
    {
        $value = trim($value);
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $value = strtolower($ascii ?: $value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value);
        return trim((string) $value, '-');
    }

    protected function dateOrNull(string $date): ?string
    {
        $date = trim($date);
        if ($date === '' || str_starts_with($date, '0000-00-00')) {
            return null;
        }

        return date('Y-m-d H:i:s', strtotime($date));
    }

    protected function mimeFromFilename(string $filename): string
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };
    }
}
