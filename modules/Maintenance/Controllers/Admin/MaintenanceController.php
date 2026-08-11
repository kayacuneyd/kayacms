<?php

namespace Maintenance\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use Maintenance\Libraries\BackupManager;
use Maintenance\Libraries\WebCron;
use Setting\Models\SettingModel;
use User\Libraries\SecurityLog;

class MaintenanceController extends BaseAdminController
{
    public function index()
    {
        if ($redirect = $this->requirePermission('maintenance.view')) {
            return $redirect;
        }

        $settings = new SettingModel();

        $this->data['active']             = 'maintenance';
        $this->data['title']              = 'Backup & Maintenance';
        $this->data['backups']            = (new BackupManager())->all();
        $this->data['maintenance_enabled'] = (bool) $settings->getSetting('maintenance_enabled', false);
        $this->data['backup_keep_count']  = (int) $settings->getSetting('backup_keep_count', 10);
        $this->data['backup_due']         = $this->lastBackupMinutes($this->data['backups']);
        $this->data['cron_token']         = (string) $settings->getSetting('cron_token', '');
        $this->data['cron_tasks']         = (string) $settings->getSetting('cron_tasks', 'media:queue,backup:create');

        return $this->render('admin/maintenance/index', $this->data);
    }

    public function createBackup()
    {
        if ($redirect = $this->requirePermission('maintenance.backup')) {
            return $redirect;
        }

        try {
            $result = (new BackupManager())->create();
            $this->logActivity('backup.created', 'backup', (int) $result['id'], 'Backup created: ' . $result['filename']);
            SecurityLog::log('backup.created', 'Backup created: ' . $result['filename'], 'info', $this->data['user']['id'] ?? null);

            return redirect()->to('/admin/maintenance')->with('success', 'Backup created: ' . $result['filename']);
        } catch (\Throwable $e) {
            return redirect()->to('/admin/maintenance')->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    public function download(int $id)
    {
        $backup = (new BackupManager())->get($id);

        if (! $backup || ! is_file($backup['path'] ?? '')) {
            return redirect()->to('/admin/maintenance')->with('error', 'Backup file not found.');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/octet-stream')
            ->setHeader('Content-Disposition', 'attachment; filename="' . basename($backup['path']) . '"')
            ->setBody(file_get_contents($backup['path']));
    }

    public function delete(int $id)
    {
        if ($redirect = $this->requirePermission('maintenance.backup')) {
            return $redirect;
        }

        $deleted = (new BackupManager())->delete($id);

        return redirect()->to('/admin/maintenance')
            ->with($deleted ? 'success' : 'error', $deleted ? 'Backup deleted.' : 'Backup not found.');
    }

    public function toggleMaintenance()
    {
        if ($redirect = $this->requirePermission('maintenance.toggle')) {
            return $redirect;
        }

        $settings = new SettingModel();
        $current  = (bool) $settings->getSetting('maintenance_enabled', false);
        $new      = (int) $this->request->getPost('enabled') ? 1 : 0;

        $settings->setSetting('maintenance_enabled', $new, 'general', 'boolean');

        SecurityLog::warning(
            'maintenance.toggle',
            'Maintenance mode ' . ($new ? 'enabled' : 'disabled'),
            $this->data['user']['id'] ?? null
        );

        return redirect()->to('/admin/maintenance')
            ->with($new ? 'success' : 'success', $new ? 'Maintenance mode enabled. Public site is now unavailable.' : 'Maintenance mode disabled.');
    }

    public function updateSettings()
    {
        if ($redirect = $this->requirePermission('maintenance.backup')) {
            return $redirect;
        }

        $keep = max(1, (int) $this->request->getPost('backup_keep_count'));

        (new SettingModel())->setSetting('backup_keep_count', $keep, 'general', 'integer');

        return redirect()->to('/admin/maintenance')->with('success', 'Backup retention updated.');
    }

    public function updateCron()
    {
        if ($redirect = $this->requirePermission('maintenance.backup')) {
            return $redirect;
        }

        $settings = new SettingModel();
        $token    = trim((string) $this->request->getPost('cron_token'));

        if (preg_match('/[^A-Za-z0-9\-_]/', $token)) {
            return redirect()->to('/admin/maintenance')->with('error', 'Cron token may only contain letters, numbers, dashes and underscores.');
        }

        $tasks = preg_replace('/[^a-z:, ]/', '', strtolower((string) $this->request->getPost('cron_tasks'))) ?? '';

        $settings->setSetting('cron_token', $token, 'system', 'string');
        $settings->setSetting('cron_tasks', trim($tasks), 'system', 'string');

        SecurityLog::warning(
            'cron.token.updated',
            $token === '' ? 'Web-cron endpoint disabled (empty token).' : 'Web-cron token updated.',
            $this->data['user']['id'] ?? null
        );

        return redirect()->to('/admin/maintenance')->with('success', 'Web cron settings saved.');
    }

    public function generateCronToken()
    {
        if ($redirect = $this->requirePermission('maintenance.backup')) {
            return $redirect;
        }

        (new WebCron())->generateToken();

        SecurityLog::warning('webcron.token.generated', 'New web-cron token generated.', $this->data['user']['id'] ?? null);

        return redirect()->to('/admin/maintenance')->with('success', 'New web-cron token generated.');
    }

    private function lastBackupMinutes(array $backups)
    {
        $last = $backups[0]['created_at'] ?? null;

        if (! $last) {
            return null;
        }

        return max(0, (int) round((time() - strtotime($last)) / 60));
    }
}