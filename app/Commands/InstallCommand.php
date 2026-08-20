<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use User\Models\UserModel;

/**
 * One-command setup wizard for a fresh clone or `composer create-project`.
 * Bootstraps .env, generates the encryption key, runs migrations + seed
 * data, and creates the first admin account.
 */
class InstallCommand extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'app:install';
    protected $description = 'Interactive setup wizard: .env, encryption key, migrations, seed data, admin account.';
    protected $usage       = 'app:install [--no-interaction] [--admin-email address] [--admin-username name] [--admin-password secret]';
    protected $options     = [
        '--no-interaction'  => 'Skip prompts; use defaults / provided options for everything.',
        '--admin-email'     => 'Admin account email, space-separated (default: admin@kayacms.local).',
        '--admin-username'  => 'Admin account username, space-separated (default: admin).',
        '--admin-password'  => 'Admin account password, space-separated (default: a random one is generated and printed).',
    ];

    public function run(array $params)
    {
        $noInteraction = (bool) CLI::getOption('no-interaction');

        CLI::write('KayaCMS setup', 'green');
        CLI::write('==============', 'green');
        CLI::newLine();

        $this->ensureEnvFile();
        $this->ensureEncryptionKey();

        if (! $noInteraction) {
            CLI::write('Make sure the database settings in .env are correct before continuing', 'yellow');
            CLI::write('(SQLite works out of the box; MySQL/Postgres need the database.default.* values set).', 'yellow');
            CLI::prompt('Press Enter to run migrations, or Ctrl+C to stop and edit .env first');
        }

        CLI::write('Running migrations...', 'cyan');
        command('migrate --all');

        CLI::write('Seeding default data (roles, admin user, settings, themes, contact form)...', 'cyan');
        $seeder = Database::seeder();
        $seeder->call(\User\Database\Seeds\UserSeeder::class);
        $seeder->call(\Setting\Database\Seeds\SettingSeeder::class);
        $seeder->call(\Theme\Database\Seeds\ThemeSeeder::class);
        $seeder->call(\Contact\Database\Seeds\ContactFormSeeder::class);

        $this->setAdminCredentials($noInteraction);

        CLI::newLine();
        CLI::write('Setup complete.', 'green');
        CLI::write('Run `php spark serve` (or point your web server at public/) and open /admin.', 'white');
    }

    private function ensureEnvFile(): void
    {
        $envPath = ROOTPATH . '.env';

        if (is_file($envPath)) {
            CLI::write('.env already exists, leaving it as-is.', 'white');

            return;
        }

        $examplePath = ROOTPATH . '.env.example';

        if (! is_file($examplePath)) {
            CLI::error('Neither .env nor .env.example found — cannot bootstrap configuration.');
            exit(1);
        }

        copy($examplePath, $envPath);
        CLI::write('Created .env from .env.example.', 'green');
    }

    private function ensureEncryptionKey(): void
    {
        $envPath = ROOTPATH . '.env';
        $env = file_get_contents($envPath) ?: '';

        if (preg_match('/^\s*encryption\.key\s*=\s*\S+/m', $env)) {
            CLI::write('Encryption key already set.', 'white');

            return;
        }

        command('key:generate');
        CLI::write('Generated a new encryption key.', 'green');
    }

    private function setAdminCredentials(bool $noInteraction): void
    {
        $users = new UserModel();
        $admin = $users->where('username', 'admin')->first();

        if (! $admin) {
            CLI::error('Expected the seeded "admin" user to exist — seeding may have failed.');

            return;
        }

        $email = CLI::getOption('admin-email');
        $username = CLI::getOption('admin-username');
        $password = CLI::getOption('admin-password');

        if (! $noInteraction) {
            $email ??= CLI::prompt('Admin email', 'admin@kayacms.local');
            $username ??= CLI::prompt('Admin username', 'admin');
            $password ??= CLI::prompt('Admin password (leave blank to generate one)');
        }

        $email ??= 'admin@kayacms.local';
        $username ??= 'admin';
        $generated = false;

        if (! $password) {
            $password = bin2hex(random_bytes(9));
            $generated = true;
        }

        // skipValidation: is_unique[...,{id}] would otherwise reject the
        // update against the row's own current values (the {id} placeholder
        // isn't resolved unless 'id' is also present as a validated field).
        $users->skipValidation(true)->update((int) $admin->id, [
            'email'    => $email,
            'username' => $username,
            'password' => $password,
        ]);

        CLI::newLine();
        CLI::write('Admin account:', 'green');
        CLI::write("  Email:    {$email}");
        CLI::write("  Username: {$username}");

        if ($generated) {
            CLI::write("  Password: {$password}", 'yellow');
            CLI::write('  (generated — save it now, it will not be shown again)', 'yellow');
        } else {
            CLI::write('  Password: (as entered)');
        }
    }
}
