<?php
namespace Newsletter\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Newsletter\Libraries\NewsletterService;

class NewsletterQueueCommand extends BaseCommand
{
    protected $group = 'Newsletter';
    protected $name = 'newsletter:queue';
    protected $description = 'Process pending newsletter queue jobs.';
    protected $usage = 'newsletter:queue [--limit 25]';

    public function run(array $params)
    {
        $limit = (int) (CLI::getOption('limit') ?: 25);
        $service = new NewsletterService();
        $scheduled = $service->enqueueDueCampaigns();
        $stats = $service->work($limit);
        CLI::write("Scheduled campaigns queued: {$scheduled}; Sent: {$stats['sent']}; Failed: {$stats['failed']}; Skipped: {$stats['skipped']}", 'green');
    }
}
