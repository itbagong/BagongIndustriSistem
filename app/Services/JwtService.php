<?php

namespace App\Services;

/**
 * JwtService — JWT HS256 tanpa library eksternal
 * Simpan secret key di .env:
 *   jwt.secretKey  = "random-string-min-32-karakter"
 *   jwt.expiration = 28800
 *   jwt.issuer     = "bagong-bis-local"
 */
class JwtService
{
    private string $secretKey;
    private int    $expiration;
    private string $issuer;

    public function __construct()
    {
        $this->secretKey  = (string) env('jwt.secretKey',  'ini-random-string-panjang-jwt-94-karakter-di-sini');
        $this->expiration = (int)    env('jwt.expiration', 28800); // default 8 jam
        $this->issuer     = (string) env('jwt.issuer',     'bagong-bis-local');
    }

    // ─────────────────────────────────────────────────────────────
    // Generate JWT token
    // ─────────────────────────────────────────────────────────────
    public function generate(array $data): string
    {
        $now = time();

        $header  = $this->b64Encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload = $this->b64Encode(json_encode([
            'iss'  => $this->issuer,
            'iat'  => $now,
            'exp'  => $now + $this->expiration,
            'data' => $data,
        ]));

        $sig = $this->sign($header . '.' . $payload);

        return $header . '.' . $payload . '.' . $sig;
    }

    // ─────────────────────────────────────────────────────────────
    // Decode + validasi token
    // Return array data-nya, atau null jika invalid/expired
    // ─────────────────────────────────────────────────────────────
    public function decode(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        [$header, $payload, $signature] = $parts;

        // 1) verifikasi signature — timing-safe
        if (! hash_equals($this->sign($header . '.' . $payload), $signature)) {
            return null;
        }

        // 2) decode payload
        $decoded = json_decode($this->b64Decode($payload), true);
        if (! is_array($decoded)) return null;

        // 3) cek expired
        if (isset($decoded['exp']) && $decoded['exp'] < time()) {
            return null;
        }

        // 4) cek issuer
        if (isset($decoded['iss']) && $decoded['iss'] !== $this->issuer) {
            return null;
        }

        return $decoded['data'] ?? $decoded;
    }

    // Cek expired tanpa full decode (ringan)
    public function isExpired(string $token): bool
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return true;

        $decoded = json_decode($this->b64Decode($parts[1]), true);
        return ! isset($decoded['exp']) || $decoded['exp'] < time();
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers private
    // ─────────────────────────────────────────────────────────────
    private function sign(string $data): string
    {
        return $this->b64Encode(hash_hmac('sha256', $data, $this->secretKey, true));
    }

    private function b64Encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function b64Decode(string $data): string
    {
        $rem = strlen($data) % 4;
        if ($rem) $data .= str_repeat('=', 4 - $rem);
        return base64_decode(strtr($data, '-_', '+/'));
    }
}