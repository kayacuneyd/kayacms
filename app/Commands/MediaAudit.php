<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class MediaAudit extends BaseCommand
{
    protected $group = 'Media';
    protected $name = 'media:audit';
    protected $description = 'Report broken media files and missing image metadata.';

    public function run(array $params)
    {
        $dir = $this->path((string) (CLI::getOption('dir') ?: WRITEPATH . 'reports'));
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $path = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . 'media-audit.csv';
        $handle = fopen($path, 'wb');
        fputcsv($handle, ['id', 'path', 'issue', 'alt_text', 'caption', 'credit', 'used_by_content']);

        $db = \Config\Database::connect();
        $media = $db->table('media')->where('deleted_at', null)->get()->getResultArray();
        $issues = 0;

        foreach ($media as $item) {
            $usedBy = $this->usedBy($db, (string) ($item['path'] ?: $item['file_path']));
            foreach ($this->issues($item) as $issue) {
                $issues++;
                fputcsv($handle, [
                    $item['id'],
                    $item['path'] ?: $item['file_path'],
                    $issue,
                    $item['alt_text'] ?? '',
                    $item['caption'] ?? '',
                    $item['credit'] ?? '',
                    implode('|', $usedBy),
                ]);
            }
        }

        fclose($handle);
        CLI::write("Media audit complete: {$issues} issues.", $issues ? 'yellow' : 'green');
        CLI::write("Media report: {$path}");
    }

    private function path(string $path): string
    {
        if (preg_match('/^[A-Za-z]:[\/\\\\]/', $path) || str_starts_with($path, '/')) {
            return $path;
        }
        return ROOTPATH . ltrim($path, '/\\');
    }

    private function issues(array $item): array
    {
        $issues = [];
        $path = ltrim((string) ($item['file_path'] ?: $item['path']), '/');
        $mime = (string) ($item['mime_type'] ?? '');
        $isImage = str_starts_with($mime, 'image/');

        if ($path === '' || ! is_file(FCPATH . $path)) {
            $issues[] = 'missing_file';
        }

        if ($isImage && trim((string) ($item['alt_text'] ?? '')) === '') {
            $issues[] = 'missing_alt_text';
        }
        if ($isImage && trim((string) ($item['caption'] ?? '')) === '') {
            $issues[] = 'missing_caption';
        }
        if ($isImage && trim((string) ($item['credit'] ?? '')) === '') {
            $issues[] = 'missing_credit';
        }
        if ($isImage && trim((string) ($item['derivatives'] ?? '')) === '') {
            $issues[] = 'missing_derivatives';
        }

        return $issues;
    }

    private function usedBy($db, string $path): array
    {
        $path = ltrim($path, '/');
        if ($path === '') {
            return [];
        }

        $rows = $db->table('content')
            ->select('id, title')
            ->groupStart()
                ->where('featured_image', $path)
                ->orWhere('featured_image', '/' . $path)
            ->groupEnd()
            ->get()
            ->getResultArray();

        return array_map(static fn ($row) => $row['id'] . ':' . $row['title'], $rows);
    }
}
