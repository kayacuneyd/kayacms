<?php
namespace Newsletter\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class NewsletterPurgeCommand extends BaseCommand
{
    protected $group = 'Newsletter';
    protected $name = 'newsletter:purge';
    protected $description = 'Backup and permanently delete suppressed/spam/non-subscribed newsletter records.';
    protected $usage = 'newsletter:purge [--apply] [--backup writable/backups/newsletter-purge.csv]';

    public function run(array $params)
    {
        $apply = (bool) CLI::getOption('apply');
        $backup = $this->path((string) (CLI::getOption('backup') ?: WRITEPATH . 'backups/newsletter-purge-' . date('Ymd-His') . '.csv'));
        $dir = dirname($backup);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $db = \Config\Database::connect();
        $builder = $db->table('newsletter_subscribers')
            ->groupStart()
                ->whereIn('quality_status', ['spam', 'suppressed'])
                ->orWhere('status !=', 'subscribed')
            ->groupEnd();

        $rows = $builder->get()->getResultArray();
        $count = count($rows);

        $handle = fopen($backup, 'wb');
        if ($rows) {
            fputcsv($handle, array_keys($rows[0]));
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
        } else {
            fputcsv($handle, ['empty']);
        }
        fclose($handle);

        CLI::write("Matched {$count} records for purge.", $count ? 'yellow' : 'green');
        CLI::write("Backup written: {$backup}");

        if (! $apply) {
            CLI::write('Dry run only. Re-run with --apply to permanently delete matched records.', 'yellow');
            return;
        }

        if ($count === 0) {
            CLI::write('Nothing to delete.', 'green');
            return;
        }

        $db->transStart();
        $ids = array_map(static fn ($row) => (int) $row['id'], $rows);
        foreach (array_chunk($ids, 500) as $chunk) {
            $db->table('newsletter_subscriber_lists')->whereIn('subscriber_id', $chunk)->delete();
            $db->table('newsletter_queue')->whereIn('subscriber_id', $chunk)->delete();
            $db->table('newsletter_subscribers')->whereIn('id', $chunk)->delete();
        }
        $db->transComplete();

        if (! $db->transStatus()) {
            CLI::error('Purge failed; transaction was rolled back.');
            return;
        }

        CLI::write("Permanently deleted {$count} newsletter records.", 'green');
    }

    private function path(string $path): string
    {
        if (preg_match('/^[A-Za-z]:[\/\\\\]/', $path) || str_starts_with($path, '/')) {
            return $path;
        }
        return ROOTPATH . ltrim($path, '/\\');
    }
}
