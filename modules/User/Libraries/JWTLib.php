<?php
namespace User\Libraries;

use Exception;

/**
 * JWTLib - Simple JWT implementation using HMAC-SHA256
 */
class JWTLib
{
    private string $secret;
    private string $issuer;
    private int $expiry;

    public function __construct()
    {
        $this->secret = env('jwt.secret', 'default_secret_change_this');
        $this->issuer = env('jwt.issuer', 'kayacms');
        $this->expiry = (int) env('jwt.expiry', 86400); // 24 hours default
    }

    /**
     * Encode payload to JWT
     */
    public function encode(array $payload): string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        $payload['iss'] = $this->issuer;
        $payload['iat'] = time();
        $payload['exp'] = time() + $this->expiry;

        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));

        $signature = hash_hmac(
            'sha256',
            $headerEncoded . '.' . $payloadEncoded,
            $this->secret,
            true
        );

        $signatureEncoded = $this->base64UrlEncode($signature);

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }

    /**
     * Decode and verify JWT
     */
    public function decode(string $token): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$headerEncoded, $payloadEncoded, $signatureProvided] = $parts;

        $signature = hash_hmac(
            'sha256',
            $headerEncoded . '.' . $payloadEncoded,
            $this->secret,
            true
        );

        $signatureEncoded = $this->base64UrlEncode($signature);

        // Verify signature
        if (!hash_equals($signatureEncoded, $signatureProvided)) {
            return null;
        }

        $payload = json_decode($this->base64UrlDecode($payloadEncoded), true);

        // Verify expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    /**
     * Verify token validity
     */
    public function verify(string $token): bool
    {
        return $this->decode($token) !== null;
    }

    /**
     * Create access token for user
     */
    public function createToken(int $userId, string $username, ?int $roleId = null): string
    {
        $payload = [
            'sub' => $userId,
            'username' => $username,
            'role_id' => $roleId,
        ];

        return $this->encode($payload);
    }

    /**
     * Base64 URL encode
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL decode
     */
    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
