<?php

namespace Rss\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Rss\Libraries\RssIdeaService;
use Rss\Models\RssItemModel;

class SuggestCommand extends BaseCommand
{
    protected $group = 'RSS';
    protected $name = 'rss:suggest';
    protected $description = 'Generate an AI draft suggestion for one RSS item.';
    protected $usage = 'rss:suggest <itemId>';

    public function run(array $params)
    {
        $id = (int) ($params[0] ?? 0);
        $item = $id ? (new RssItemModel())->find($id) : null;
        if (! $item) {
            CLI::error('RSS item not found.');
            return;
        }

        try {
            $suggestion = (new RssIdeaService())->suggest($item);
            CLI::write(json_encode($suggestion, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'green');
        } catch (\Throwable $e) {
            CLI::error($e->getMessage());
        }
    }
}
