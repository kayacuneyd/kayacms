<?php

namespace Media\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Media\Libraries\MediaQueue;
use User\Libraries\SecurityLog;

class MediaQueueCommand extends BaseCommand
{
    protected $group       = 'Media';
    protected $name        = 'media:queue';
    protected $description = 'Process pending media jobs (thumbnails, resizes). Schedule via cron.';
    protected $usage       = 'media:queue [--limit=N] [--type=TYPE]';
    public    $options     = [
        '--limit' => 'Max jobs to process in this run (default: 10).',
        '--type'  => 'Only process jobs of this type (thumbnail|resize).',
    ];

    public function run(array $params)
    {
        $limit = (int) ($params['limit'] ?? 10);
        $limit = max(1, $limit);
        $type  = ($params['type'] ?? '');

        CLI::write('Processing media queue...', 'green');

        $queue   = new MediaQueue();
        $summary = $queue->work($limit, $type !== '' ? $type : null, 'cli');

        if ($summary['claimed'] === 0) {
            CLI::write('No pending jobs.', 'yellow');
        }

        CLI::write("Claimed: {$summary['claimed']}, done: {$summary['done']}, failed: {$summary['failed']}, skipped: {$summary['skipped']}");

        if ($summary['failed'] > 0) {
            SecurityLog::warning(
                'media.queue.failed',
                "Media queue: {$summary['failed']} job(s) failed in this run."
            );
        }

        CLI::write('Done.', 'green');
    }
}