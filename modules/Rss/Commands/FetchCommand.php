<?php

namespace Rss\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Rss\Libraries\RssFetcher;

class FetchCommand extends BaseCommand
{
    protected $group = 'RSS';
    protected $name = 'rss:fetch';
    protected $description = 'Fetch active RSS sources into the idea pool.';

    public function run(array $params)
    {
        foreach ((new RssFetcher())->fetchAll() as $result) {
            $color = $result['ok'] ? 'green' : 'red';
            CLI::write(($result['ok'] ? 'OK ' : 'ERR ') . $result['source'] . ' +' . $result['inserted'] . ($result['error'] ? ' - ' . $result['error'] : ''), $color);
        }
    }
}
