<?php

// Ensure current directory is project root
chdir(__DIR__ . '/..');

// Load composer autoloader
require_once 'vendor/autoload.php';

// Load CodeIgniter bootstrap for testing
require_once 'vendor/codeigniter4/framework/system/Test/bootstrap.php';

// Ensure test database exists and migrations are run
$paths = new \Config\Paths();
$dbTestPath = WRITEPATH . 'db/test.sqlite3';

if (! file_exists($dbTestPath)) {
    @mkdir(dirname($dbTestPath), 0777, true);
    touch($dbTestPath);
}

$runner = \Config\Services::migrations();
$runner->setNamespace(null)->latest();
