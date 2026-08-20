<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class EncodingAudit extends BaseCommand
{
    protected $group = 'System';
    protected $name = 'encoding:audit';
    protected $description = 'Scan frontend files for mojibake strings before deployment.';
    protected $usage = 'encoding:audit [--path app/Views/themes]';

    private array $extensions = ['php', 'js', 'css'];

    public function run(array $params)
    {
        $paths = CLI::getOption('path');
        if ($paths === null) {
            $paths = [
                'app/Views/themes',
                'public/assets/themes',
            ];
        } elseif (! is_array($paths)) {
            $paths = [$paths];
        }

        $findings = [];
        foreach ($paths as $path) {
            $absolute = $this->path((string) $path);
            if (is_file($absolute)) {
                $this->scanFile($absolute, $findings);
                continue;
            }
            if (is_dir($absolute)) {
                $this->scanDirectory($absolute, $findings);
            }
        }

        foreach ($findings as $finding) {
            CLI::write($finding, 'yellow');
        }

        if ($findings) {
            CLI::error('Encoding audit failed: ' . count($findings) . ' possible mojibake occurrence(s).');
            return EXIT_ERROR;
        }

        CLI::write('Encoding audit clean.', 'green');
        return EXIT_SUCCESS;
    }

    private function scanDirectory(string $directory, array &$findings): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file instanceof SplFileInfo && $file->isFile()) {
                $this->scanFile($file->getPathname(), $findings);
            }
        }
    }

    private function scanFile(string $path, array &$findings): void
    {
        if (! in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), $this->extensions, true)) {
            return;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return;
        }

        $lines = preg_split('/\R/', $content) ?: [];
        foreach ($lines as $lineNumber => $line) {
            if (preg_match('/(?:Ã|Ä|Å|Â|â€)/u', $line)) {
                $relative = str_replace(ROOTPATH, '', $path);
                $snippet = trim(preg_replace('/\s+/', ' ', $line) ?? $line);
                $findings[] = $relative . ':' . ($lineNumber + 1) . ': ' . mb_substr($snippet, 0, 180);
            }
        }
    }

    private function path(string $path): string
    {
        if (preg_match('/^[A-Za-z]:[\/\\\\]/', $path) || str_starts_with($path, '/')) {
            return $path;
        }

        return ROOTPATH . ltrim($path, '/\\');
    }
}
