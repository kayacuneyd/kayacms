<?php
namespace Media\Libraries;

use RuntimeException;
use Setting\Models\SettingModel;

class MediaStorage
{
    private array $settings;

    public function __construct(?SettingModel $settings = null)
    {
        $model = $settings ?? new SettingModel();
        $this->settings = $model->getByGroup('storage');
    }

    public function provider(): string
    {
        return $this->r2Ready() ? 'r2' : 'local';
    }

    public function r2Ready(): bool
    {
        return ($this->settings['storage_provider'] ?? 'local') === 'r2'
            && $this->value('r2_access_key_id') !== ''
            && $this->value('r2_secret_access_key') !== ''
            && $this->value('r2_bucket') !== ''
            && $this->value('r2_public_base_url') !== ''
            && $this->endpoint() !== '';
    }

    public function upload(string $source, string $key, string $mimeType): array
    {
        if (! $this->r2Ready()) {
            return [
                'provider' => 'local',
                'key' => $key,
                'public_url' => '',
            ];
        }

        $this->putR2($source, $key, $mimeType);

        return [
            'provider' => 'r2',
            'key' => $key,
            'public_url' => rtrim($this->value('r2_public_base_url'), '/') . '/' . ltrim($key, '/'),
        ];
    }

    public function delete(?string $provider, ?string $key): void
    {
        if ($provider !== 'r2' || ! $key || ! $this->r2Ready()) {
            return;
        }

        $this->requestR2('DELETE', $key, '', 'application/octet-stream');
    }

    private function putR2(string $source, string $key, string $mimeType): void
    {
        if (! is_file($source)) {
            throw new RuntimeException('Upload source file not found.');
        }

        $body = file_get_contents($source);
        if ($body === false) {
            throw new RuntimeException('Upload source file could not be read.');
        }

        $this->requestR2('PUT', $key, $body, $mimeType);
    }

    private function requestR2(string $method, string $key, string $body, string $mimeType): void
    {
        if (! function_exists('curl_init')) {
            throw new RuntimeException('cURL extension is required for R2 uploads.');
        }

        $bucket = $this->value('r2_bucket');
        $endpoint = rtrim($this->endpoint(), '/');
        $encodedKey = str_replace('%2F', '/', rawurlencode(ltrim($key, '/')));
        $url = $endpoint . '/' . rawurlencode($bucket) . '/' . $encodedKey;
        $host = parse_url($endpoint, PHP_URL_HOST) ?: '';
        $amzDate = gmdate('Ymd\THis\Z');
        $date = gmdate('Ymd');
        $payloadHash = hash('sha256', $body);
        $canonicalUri = '/' . rawurlencode($bucket) . '/' . $encodedKey;
        $canonicalHeaders = "host:{$host}\n" . "x-amz-content-sha256:{$payloadHash}\n" . "x-amz-date:{$amzDate}\n";
        $signedHeaders = 'host;x-amz-content-sha256;x-amz-date';
        $canonicalRequest = "{$method}\n{$canonicalUri}\n\n{$canonicalHeaders}\n{$signedHeaders}\n{$payloadHash}";
        $scope = "{$date}/auto/s3/aws4_request";
        $stringToSign = "AWS4-HMAC-SHA256\n{$amzDate}\n{$scope}\n" . hash('sha256', $canonicalRequest);
        $signature = hash_hmac('sha256', $stringToSign, $this->signingKey($date));
        $authorization = 'AWS4-HMAC-SHA256 Credential=' . $this->value('r2_access_key_id') . '/' . $scope . ', SignedHeaders=' . $signedHeaders . ', Signature=' . $signature;

        $headers = [
            'Authorization: ' . $authorization,
            'Content-Type: ' . $mimeType,
            'Host: ' . $host,
            'x-amz-content-sha256: ' . $payloadHash,
            'x-amz-date: ' . $amzDate,
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => 60,
        ]);

        if ($method !== 'DELETE') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $status < 200 || $status >= 300) {
            throw new RuntimeException('R2 request failed with status ' . $status . ($error ? ': ' . $error : '.'));
        }
    }

    private function signingKey(string $date): string
    {
        $secret = 'AWS4' . $this->value('r2_secret_access_key');
        $kDate = hash_hmac('sha256', $date, $secret, true);
        $kRegion = hash_hmac('sha256', 'auto', $kDate, true);
        $kService = hash_hmac('sha256', 's3', $kRegion, true);

        return hash_hmac('sha256', 'aws4_request', $kService, true);
    }

    private function endpoint(): string
    {
        $endpoint = $this->value('r2_endpoint');
        if ($endpoint !== '') {
            return $endpoint;
        }

        $account = $this->value('r2_account_id');
        return $account !== '' ? 'https://' . $account . '.r2.cloudflarestorage.com' : '';
    }

    private function value(string $key): string
    {
        return trim((string) ($this->settings[$key] ?? ''));
    }
}
