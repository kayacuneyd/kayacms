<?php

/**
 * Release helper: bumps app/Config/Version.php, drafts a CHANGELOG.md
 * entry from the commit log since the last tag, commits, and creates a
 * git tag. Does not push — review the diff, then `git push && git push --tags`.
 *
 * Usage: composer run release -- <patch|minor|major> ["Release title"]
 */

$root = dirname(__DIR__);
$level = $argv[1] ?? null;
$title = $argv[2] ?? null;

if (! in_array($level, ['patch', 'minor', 'major'], true)) {
    fwrite(STDERR, "Usage: composer run release -- <patch|minor|major> [\"Release title\"]\n");
    exit(1);
}

$versionFile = $root . '/app/Config/Version.php';
$source = file_get_contents($versionFile);

preg_match('/MAJOR\s*=\s*(\d+);/', $source, $mMajor);
preg_match('/MINOR\s*=\s*(\d+);/', $source, $mMinor);
preg_match('/PATCH\s*=\s*(\d+);/', $source, $mPatch);

if (! $mMajor || ! $mMinor || ! $mPatch) {
    fwrite(STDERR, "Could not parse current version from {$versionFile}\n");
    exit(1);
}

$major = (int) $mMajor[1];
$minor = (int) $mMinor[1];
$patch = (int) $mPatch[1];

switch ($level) {
    case 'major':
        $major++;
        $minor = 0;
        $patch = 0;
        break;
    case 'minor':
        $minor++;
        $patch = 0;
        break;
    case 'patch':
        $patch++;
        break;
}

$newVersion = "{$major}.{$minor}.{$patch}";

echo "Bumping version to {$newVersion} ({$level})...\n";

$source = preg_replace('/MAJOR\s*=\s*\d+;/', "MAJOR = {$major};", $source, 1);
$source = preg_replace('/MINOR\s*=\s*\d+;/', "MINOR = {$minor};", $source, 1);
$source = preg_replace('/PATCH\s*=\s*\d+;/', "PATCH = {$patch};", $source, 1);
file_put_contents($versionFile, $source);

// Draft a CHANGELOG entry from commits since the last tag.
$lastTag = trim((string) shell_exec('git -C ' . escapeshellarg($root) . ' describe --tags --abbrev=0 2>/dev/null'));
$range = $lastTag !== '' ? "{$lastTag}..HEAD" : '';
$log = trim((string) shell_exec(
    'git -C ' . escapeshellarg($root) . ' log ' . escapeshellarg($range) . ' --oneline --no-merges 2>/dev/null'
));

$date = date('Y-m-d');
$heading = $title ? "## [{$newVersion}] - {$date} — {$title}" : "## [{$newVersion}] - {$date}";

$draftLines = ["### Changed since {$lastTag}\n"];
foreach (array_filter(explode("\n", $log)) as $line) {
    // Strip the leading short hash, keep the message.
    $message = preg_replace('/^[0-9a-f]{7,}\s+/', '', $line);
    $draftLines[] = '- ' . $message;
}
$draft = $heading . "\n\n" . implode("\n", $draftLines) . "\n";

$changelogFile = $root . '/CHANGELOG.md';
$changelog = file_get_contents($changelogFile);
$changelog = preg_replace('/(follows \[Semantic Versioning\]\([^)]+\)\.\n)/', "$1\n{$draft}", $changelog, 1);
file_put_contents($changelogFile, $changelog);

echo 'Drafted a CHANGELOG.md entry from ' . (count($draftLines) - 1) . " commit(s) since {$lastTag}.\n";

fwrite(STDOUT, "\nReview app/Config/Version.php and CHANGELOG.md now if you want to edit the draft prose.\n");
fwrite(STDOUT, "Press Enter to commit + tag v{$newVersion}, or Ctrl+C to abort and edit first: ");
$confirm = fgets(STDIN);

$run = static function (string $cmd) use ($root): void {
    passthru('git -C ' . escapeshellarg($root) . ' ' . $cmd, $code);
    if ($code !== 0) {
        fwrite(STDERR, "Command failed: git {$cmd}\n");
        exit($code);
    }
};

$run('add app/Config/Version.php CHANGELOG.md');
$run('commit -m ' . escapeshellarg("chore(release): v{$newVersion}"));
$run('tag ' . escapeshellarg("v{$newVersion}"));

echo "\nTagged v{$newVersion}. Push when ready:\n";
echo "  git push && git push --tags\n";
echo "\nPushing the tag triggers the release.yml workflow, which builds a zip and publishes a GitHub Release.\n";
