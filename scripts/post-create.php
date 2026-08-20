<?php

/**
 * Runs automatically after `composer create-project kayacms/kayacms`
 * (registered as composer.json's post-create-project-cmd). Bootstraps
 * just enough to get to `php spark app:install`: creates .env from
 * .env.example and generates an encryption key. Does NOT touch the
 * database — the user needs to set database.default.* in .env first
 * (SQLite works with the defaults; MySQL/Postgres need real values).
 */

$root = dirname(__DIR__);
$env = $root . '/.env';
$example = $root . '/.env.example';

echo "\nKayaCMS: post-install setup\n";
echo "============================\n";

if (! is_file($env) && is_file($example)) {
    copy($example, $env);
    echo "Created .env from .env.example.\n";
} elseif (is_file($env)) {
    echo ".env already exists, leaving it as-is.\n";
}

$hasKey = is_file($env) && preg_match('/^\s*encryption\.key\s*=\s*\S+/m', file_get_contents($env));

if (! $hasKey) {
    passthru('php ' . escapeshellarg($root . '/spark') . ' key:generate', $code);
    if ($code === 0) {
        echo "Generated an encryption key.\n";
    }
}

echo "\nNext steps:\n";
echo "  1. Check the database.default.* settings in .env (SQLite works out of the box).\n";
echo "  2. Run: php spark app:install\n\n";
