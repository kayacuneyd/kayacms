<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class MediaFix extends BaseCommand
{
    protected $group = 'Media';
    protected $name = 'media:fix';
    protected $description = 'Apply safe media metadata fixes from content titles.';

    public function run(array $params)
    {
        $apply = (bool) CLI::getOption('apply');
        $db = \Config\Database::connect();
        $media = $db->table('media')
            ->where('deleted_at', null)
            ->like('mime_type', 'image/', 'after')
            ->get()
            ->getResultArray();

        $updates = 0;
        foreach ($media as $item) {
            if (trim((string) ($item['alt_text'] ?? '')) !== '') {
                continue;
            }

            $title = $this->contentTitleFor($db, (string) ($item['path'] ?: $item['file_path']));
            if ($title === '') {
                continue;
            }

            $updates++;
            if ($apply) {
                $db->table('media')->where('id', (int) $item['id'])->update(['alt_text' => $title]);
            }
        }

        CLI::write(($apply ? 'Applied' : 'Would apply') . " alt-text fixes to {$updates} media rows.", $updates ? 'yellow' : 'green');
        if (! $apply) {
            CLI::write('Dry run only. Re-run with --apply to write changes.');
        }
    }

    private function contentTitleFor($db, string $path): string
    {
        $path = ltrim($path, '/');
        if ($path === '') {
            return '';
        }

        $row = $db->table('content')
            ->select('title')
            ->groupStart()
                ->where('featured_image', $path)
                ->orWhere('featured_image', '/' . $path)
            ->groupEnd()
            ->orderBy('published_at', 'DESC')
            ->get()
            ->getRowArray();

        return (string) ($row['title'] ?? '');
    }
}
