<?php
namespace Newsletter\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Newsletter\Models\SubscriberModel;

class NewsletterAuditCommand extends BaseCommand
{
    protected $group = 'Newsletter';
    protected $name = 'newsletter:audit';
    protected $description = 'Score newsletter subscriber quality and optionally suppress risky/spam records.';
    protected $usage = 'newsletter:audit [--apply] [--export writable/reports/newsletter-audit.csv]';

    private array $disposableDomains = [
        'mailinator.com', '10minutemail.com', 'guerrillamail.com', 'tempmail.com',
        'temp-mail.org', 'yopmail.com', 'trashmail.com', 'getnada.com', 'sharklasers.com',
        'example.com', 'test.com',
    ];

    private array $rolePrefixes = [
        'admin', 'administrator', 'info', 'contact', 'support', 'sales', 'office',
        'test', 'demo', 'noreply', 'no-reply', 'webmaster', 'postmaster',
    ];

    public function run(array $params)
    {
        $apply = (bool) CLI::getOption('apply');
        $export = (string) (CLI::getOption('export') ?: WRITEPATH . 'reports/newsletter-audit.csv');
        $dir = dirname($export);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $model = new SubscriberModel();
        $handle = fopen($export, 'wb');
        fputcsv($handle, ['id', 'email', 'status', 'quality_status', 'quality_score', 'reasons']);

        $stats = ['valid' => 0, 'review' => 0, 'spam' => 0, 'suppressed' => 0, 'total' => 0];
        $lastId = 0;
        $limit = 1000;
        $now = date('Y-m-d H:i:s');

        do {
            $rows = $model->where('id >', $lastId)->orderBy('id', 'ASC')->findAll($limit);
            foreach ($rows as $row) {
                $lastId = (int) $row['id'];
                $stats['total']++;
                $audit = $this->score((string) $row['email'], (string) $row['status'], (string) ($row['source'] ?? ''));
                $stats[$audit['quality_status']]++;

                $domain = substr(strrchr((string) $row['email'], '@') ?: '', 1) ?: null;
                fputcsv($handle, [
                    $row['id'],
                    $row['email'],
                    $row['status'],
                    $audit['quality_status'],
                    $audit['quality_score'],
                    implode('|', $audit['reasons']),
                ]);

                if ($apply) {
                    $payload = [
                        'email_domain' => $domain,
                        'quality_status' => $audit['quality_status'],
                        'quality_score' => $audit['quality_score'],
                        'quality_reasons' => json_encode($audit['reasons'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'reviewed_at' => $now,
                    ];
                    if (in_array($audit['quality_status'], ['spam', 'suppressed'], true)) {
                        $payload['suppressed_at'] = $row['suppressed_at'] ?? $now;
                    }
                    $model->update((int) $row['id'], $payload);
                }
            }
        } while (count($rows) === $limit);

        fclose($handle);
        CLI::write("Newsletter audit complete: {$stats['total']} rows. valid={$stats['valid']}, review={$stats['review']}, spam={$stats['spam']}, suppressed={$stats['suppressed']}.", 'green');
        CLI::write("Report: {$export}");
    }

    private function score(string $email, string $status, string $source): array
    {
        $email = strtolower(trim($email));
        $local = strstr($email, '@', true) ?: '';
        $domain = substr(strrchr($email, '@') ?: '', 1);
        $score = 0;
        $reasons = [];

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $score += 100;
            $reasons[] = 'invalid_email';
        }

        if ($status !== 'subscribed') {
            $score += 80;
            $reasons[] = 'not_subscribed';
        }

        if (in_array($domain, $this->disposableDomains, true)) {
            $score += 80;
            $reasons[] = 'disposable_domain';
        }

        if (in_array($local, $this->rolePrefixes, true)) {
            $score += 35;
            $reasons[] = 'role_account';
        }

        if (preg_match('/^[a-z0-9]{14,}$/', $local) && preg_match('/\d/', $local)) {
            $score += 45;
            $reasons[] = 'random_local_part';
        }

        if (preg_match('/(.)\1{4,}/', $local) || str_contains($email, '+test')) {
            $score += 35;
            $reasons[] = 'synthetic_pattern';
        }

        if ($domain === '' || substr_count($domain, '.') === 0) {
            $score += 50;
            $reasons[] = 'weak_domain';
        }

        if (str_contains($source, 'mailpoet') && $status === 'pending') {
            $score += 25;
            $reasons[] = 'mailpoet_pending_import';
        }

        if ($status === 'unsubscribed' || $status === 'bounced') {
            return ['quality_status' => 'suppressed', 'quality_score' => max($score, 100), 'reasons' => $reasons ?: ['suppressed_status']];
        }

        if ($score >= 80) {
            return ['quality_status' => 'spam', 'quality_score' => $score, 'reasons' => $reasons];
        }

        if ($score >= 35) {
            return ['quality_status' => 'review', 'quality_score' => $score, 'reasons' => $reasons];
        }

        return ['quality_status' => 'valid', 'quality_score' => $score, 'reasons' => $reasons ?: ['clean']];
    }
}
