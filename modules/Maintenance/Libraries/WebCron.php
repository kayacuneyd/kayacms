<?php

namespace Maintenance\Libraries;

use Maintenance\Libraries\BackupManager;
use Media\Libraries\MediaQueue;
use Rss\Libraries\RssFetcher;
use Setting\Models\SettingModel;
use User\Libraries\SecurityLog;

/**
 * Web-cron service.
 *
 * Provides a token-protected, HTTP-only scheduler for shared hosting where
 * shell cron is unavailable. The token is stored in the `cron_token` setting;
 * an empty token disables the endpoint entirely (403), so a fresh install is
 * locked down by default until the operator generates a token.
 */
class WebCron
{
    private SettingModel $settings;

    public function __construct(?SettingModel $settings = null)
    {
        $this->settings = $settings ?? new SettingModel();
    }

    /**
     * Generate a fresh random token (48 hex chars = 192 bits of entropy).
     */
    public function generateToken(): string
    {
        $token = bin2hex(random_bytes(24));

        $this->settings->setSetting('cron_token', $token, 'system', 'string');

        return $token;
    }

    /**
     * List the configured tasks.
     */
    public function tasks(): array
    {
        $raw = (string) $this->settings->getSetting('cron_tasks', 'media:queue,backup:create');

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    /**
     * Validate a supplied token against the configured one.
     */
    public function isValidToken(?string $token): bool
    {
        $expected = (string) $this->settings->getSetting('cron_token', '');

        return $expected !== '' && $token !== null && hash_equals($expected, $token);
    }

    /**
     * Run all configured tasks and return per-task results.
     */
    public function run(): array
    {
        $results = [];

        foreach ($this->tasks() as $task) {
            switch ($task) {
                case 'media:queue':
                    $results['media:queue'] = (new MediaQueue())->work(10);
                    break;

                case 'backup:create':
                    $manager = new BackupManager();
                    $backup  = $manager->create();
                    $manager->prune((int) $this->settings->getSetting('backup_keep_count', 10));

                    $results['backup:create'] = [
                        'filename' => $backup['filename'],
                        'size'     => is_file($backup['path']) ? filesize($backup['path']) : 0,
                    ];
                    break;

                case 'rss:fetch':
                    $results['rss:fetch'] = (new RssFetcher())->fetchAll();
                    break;

                default:
                    $results[$task] = ['error' => "Unknown task: {$task}"];
            }
        }

        SecurityLog::info('cron.run', 'Web cron executed: ' . implode(', ', $this->tasks()));

        return $results;
    }
}