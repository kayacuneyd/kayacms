<?php

namespace Maintenance\Config;

use CodeIgniter\Config\BaseConfig;

class Maintenance extends BaseConfig
{
    /**
     * Backup directory path (relative to WRITEPATH).
     */
    public string $backupDirectory = WRITEPATH . 'backups';

    /**
     * Default number of backups to retain when pruning.
     */
    public int $backupKeepCount = 10;
}