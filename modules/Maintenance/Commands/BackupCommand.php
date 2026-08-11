<?php

namespace Maintenance\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Maintenance\Libraries\BackupManager;
use Setting\Models\SettingModel;
use User\Libraries\SecurityLog;

class BackupCommand extends BaseCommand
{
    protected $group       = 'Backup';
    protected $name        = 'backup:create';
    protected $description = 'Create a database backup snapshot (schedulable via cron).';
    protected $usage       = 'backup:create [--keep=N]';
    public    $options     = [
        '--keep' => 'Prune within do to the N most recent (default: backup_keep_count setting or 10).',
    ];

    public function run(array $params)
    {
        $manager = new BackupManager();
        $keep    = (int) ($params['keep'] ?? 0);
        $keep    = $keep > 0
            ? $keep
            : (int) ((new SettingModel())->getSetting('backup_keep_count', 10));
        $keep    = max(1, $keep);

        CLI::write('Creating database backup...', 'green');
        $result = $manager->create();

        CLI::write('Backup created: ' . $result['filename'], 'yellow');
        CLI::write('Size: ' . number_format(filesize($result['path']) / 1024, 2) . ' KB');

        SecurityLog::info('backup.created', 'Backup created via CLI: ' . basename($result['filename']), null, [
            'size' => filesize($result['path']),
        ]);

        $pruned = $manager->prune($keep);
        if ($pruned > 0) {
            CLI::write("Pruned {$pruned} old backup(s).", 'yellow');
        }

        CLI::write('Done.', 'green');
    }
}