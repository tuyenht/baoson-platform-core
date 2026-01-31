<?php

namespace App\Support\TwoFactor;

use RuntimeException;

/**
 * Minimal RFC6238 TOTP implementation (SHA1, 30s, 6 digits).
 * No external dependencies.
 */
final class Totp
{
    private const TIME_STEP = 30;
    private const DIGITS = 6;

    /**
     * Generate a Base32 secret suitable for authenticator apps.
     */
    public static function generateSecret(int $bytes = 20): string
    {
        $raw = random_bytes($bytes);
        return self::base32Encode($raw);
    }

    /**
     * Build an otpauth:// URL to render as QR code.
     */
    public static function otpAuthUrl(string $issuer, string $accountName, string $secret): string
    {
        $issuerEnc = rawurlencode($issuer);
        $label = rawurlencode($issuer.':'.$accountName);

        return "otpauth://totp/{$label}?secret={$secret}&issuer={$issuerEnc}&algorithm=SHA1&digits=".self::DIGITS.'&period='.self::TIME_STEP;
    }

    /**
     * Verify a TOTP code with a small time-window.
     */
    public static function verify(string $secretBase32, string $code, int $window = 1, ?int $timestamp = null): bool
    {
        $code = preg_replace('/\s+/', '', $code ?? '');
        if (! is_string($code) || $code === '' || ! ctype_digit($code)) {
            return false;
        }

        $timestamp ??= time();
        $counter = (int) floor($timestamp / self::TIME_STEP);

        $expectedLen = self::DIGITS;
        $code = str_pad($code, $expectedLen, '0', STR_PAD_LEFT);

        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals(self::hotp($secretBase32, $counter + $i), $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * HOTP (RFC4226) used by TOTP.
     */
    private static function hotp(string $secretBase32, int $counter): string
    {
        $key = self::base32Decode($secretBase32);

        // 8-byte counter (big-endian)
        $binCounter = pack('N*', 0).pack('N*', $counter);
        $hash = hash_hmac('sha1', $binCounter, $key, true);

        $offset = ord($hash[19]) & 0x0f;
        $slice = substr($hash, $offset, 4);
        $value = unpack('N', $slice)[1] & 0x7fffffff;

        $mod = 10 ** self::DIGITS;
        $otp = (string) ($value % $mod);

        return str_pad($otp, self::DIGITS, '0', STR_PAD_LEFT);
    }

    private static function base32Encode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $bits = '';

        foreach (str_split($data) as $c) {
            $bits .= str_pad(decbin(ord($c)), 8, '0', STR_PAD_LEFT);
        }

        $chunks = str_split($bits, 5);
        $out = '';
        foreach ($chunks as $chunk) {
            if (strlen($chunk) < 5) {
                $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            }
            $out .= $alphabet[bindec($chunk)];
        }

        // pad to multiple of 8
        while ((strlen($out) % 8) !== 0) {
            $out .= '=';
        }

        return $out;
    }

    private static function base32Decode(string $base32): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $base32 = strtoupper($base32);
        $base32 = preg_replace('/[^A-Z2-7=]/', '', $base32 ?? '');

        if (! is_string($base32) || $base32 === '') {
            throw new RuntimeException('Invalid base32 secret.');
        }

        $base32 = rtrim($base32, '=');

        $bits = '';
        foreach (str_split($base32) as $c) {
            $pos = strpos($alphabet, $c);
            if ($pos === false) {
                throw new RuntimeException('Invalid base32 secret.');
            }
            $bits .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }

        $bytes = str_split($bits, 8);
        $out = '';
        foreach ($bytes as $byte) {
            if (strlen($byte) < 8) {
                continue;
            }
            $out .= chr(bindec($byte));
        }

        return $out;
    }
}


