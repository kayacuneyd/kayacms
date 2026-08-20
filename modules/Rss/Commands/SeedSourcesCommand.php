<?php

namespace Rss\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Rss\Libraries\RssSourceSeeder;

class SeedSourcesCommand extends BaseCommand
{
    protected $group = 'RSS';
    protected $name = 'rss:seed-sources';
    protected $description = 'Seed RSS idea pool sources idempotently (edit RssSourceSeeder::sources() with your own feeds first).';

    public function run(array $params)
    {
        $result = (new RssSourceSeeder())->seed();
        CLI::write('RSS sources ready. Created: ' . $result['created'] . ', updated: ' . $result['updated'], 'green');
    }
}
