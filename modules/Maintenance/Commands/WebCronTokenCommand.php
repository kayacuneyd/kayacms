<?php

namespace Maintenance\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Maintenance\Libraries\WebCron;
use Setting\Models\SettingModel;

class WebCronTokenCommand extends BaseCommand
{
    protected $group       = 'Backup';
    protected $name        = 'webcron:token';
    protected $description = 'Show or regenerate the web-cron token (token-protected HTTP scheduler endpoint).';
    protected $usage       = 'webcron:token [--generate]';
    public    $options     = [
        '--generate' => 'Generate a new random token instead of showing the current one.',
    ];

    public function run(array $params)
    {
        $webcron = new WebCron();

        if (array_key_exists('generate', $params)) {
            $token = $webcron->generateToken();
            CLI::write('New web-cron token generated.', 'green');
            CLI::write('Token: ' . $token, 'yellow');
            CLI::write('Endpoint: ' . base_url('/cron/run/' . $token), 'yellow');
            CLI::write('Schedule it remotely with HTTP GET (no shell cron needed).', 'light_gray');

            return;
        }

        $token = (string) (new SettingModel())->getSetting('cron_token', '');

        if ($token === '') {
            CLI::write('No web-cron token set. Run `php spark webcron:token --generate` to enable the endpoint.', 'yellow');
            CLI::write('While empty the endpoint returns 403 (disabled).', 'light_gray');

            return;
        }

        CLI::write('Web-cron token: ' . $token, 'yellow');
        CLI::write('Endpoint: ' . base_url('/cron/run/' . $token), 'yellow');
    }
}