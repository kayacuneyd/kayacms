<?php

namespace User\Libraries;

/**
 * RFC 6238 TOTP implementation for two-factor authentication.
 * Generates shared secrets, provisioning URIs and time-based codes
 * using the HMAC-SHA1 algorithm.
 */
class TwoFactorAuth
{
    public const PERIOD   = 30;
    public const DIGITS   = 6;
    public const ISSUER   = 'KayaCMS';

    /**
     * Generate a new base32 shared secret (16 bytes => 26 chars).
     */
    public function generateSecret(int $length = 16): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';

        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }

        return $secret;
    }

    /**
     * Validate a user-supplied code against the TOTP secret.
     */
    public function verify(string $secret, string $code, ?int $timestamp = null): bool
    {
        if (! $secret || $code === '') {
            return false;
        }

        $timestamp ??= time();

        // Allow a window of ±1 time step to accommodate clock skew.
        foreach ([-1, 0, 1] as $window) {
            $expected = $this->generateCode($secret, $timestamp + ($window * self::PERIOD));
            if (hash_equals($expected, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate the current TOTP code for a secret.
     */
    public function generateCode(string $secret, ?int $timestamp = null): string
    {
        $timestamp ??= time();
        $counter = floor($timestamp / self::PERIOD);
        $key     = $this->base32Decode($secret);
        $counterBin = pack('N*', 0) . pack('N*', $counter);
        $hash = hash_hmac('sha1', $counterBin, $key, true);
        $offset = ord($hash[19]) & 0x0f;
        $value = (
            ((ord($hash[$offset]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        );
        $mod = $value % (10 ** self::DIGITS);

        return str_pad((string) $mod, self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * Build the otpauth:// URI for authenticator app QR encoding.
     */
    public function provisioningUri(string $secret, string $account): string
    {
        $account = rawurlencode($account);

        return "otpauth://totp/" . self::ISSUER . ":" . $account
            . "?secret={$secret}&issuer=" . rawurlencode(self::ISSUER)
            . "&algorithm=SHA1&digits=" . self::DIGITS . "&period=" . self::PERIOD;
    }

    /**
     * Default time-step remaining seconds (for displaying expiry).
     */
    public function secondsRemaining(?int $timestamp = null): int
    {
        $timestamp ??= time();
        $counter = $timestamp / self::PERIOD;
        $elapsed = ($counter - floor($counter)) * self::PERIOD;

        return (int) (self::PERIOD - $elapsed);
    }

    protected function base32Decode(string $b32): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $b32 = strtoupper($b32);
        $bits = 0;
        $value = 0;
        $out = '';

        for ($i = 0, $len = strlen($b32); $i < $len; $i++) {
            $pos = strpos($alphabet, $b32[$i]);
            if ($pos === false) {
                continue;
            }
            $value = ($value << 5) | $pos;
            $bits += 5;

            if ($bits >= 8) {
                $out .= chr(($value >> ($bits - 8)) & 0xff);
                $bits -= 8;
            }
        }

        return $out;
    }

    /**
     * Store a pending TOTP challenge in the session (user must verify)
     * before the full session is established.
     */
    public function startChallenge(int $userId): void
    {
        session()->set([
            'totp_pending_user' => $userId,
            'totp_verified'     => false,
            'totp_started'      => time(),
        ]);
    }
}