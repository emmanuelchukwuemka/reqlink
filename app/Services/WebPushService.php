<?php

namespace App\Services;

use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;

/**
 * Minimal, dependency-free Web Push (VAPID) sender.
 *
 * Sends empty-payload pushes only (no RFC8291 message encryption) — the
 * service worker shows a generic notification on any 'push' event and the
 * app takes over once opened. This avoids needing ECDH+HKDF+AES-GCM payload
 * encryption, which is real cryptographic work not worth hand-rolling
 * without a well-tested library, especially on a host where a subtle bug
 * would fail silently with no useful error.
 */
class WebPushService
{
    protected static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected static function derSignatureToRaw(string $der): ?string
    {
        // ECDSA DER: SEQUENCE { INTEGER r, INTEGER s }
        if (ord($der[0]) !== 0x30) return null;
        $offset = 2;
        if (ord($der[1]) & 0x80) {
            $offset += ord($der[1]) & 0x7F;
        }

        $readInt = function (string $der, int &$offset): ?string {
            if (ord($der[$offset]) !== 0x02) return null;
            $offset++;
            $len = ord($der[$offset]);
            $offset++;
            $bytes = substr($der, $offset, $len);
            $offset += $len;
            // Strip leading zero padding byte(s), then left-pad to 32 bytes
            $bytes = ltrim($bytes, "\x00");
            return str_pad($bytes, 32, "\x00", STR_PAD_LEFT);
        };

        $r = $readInt($der, $offset);
        $s = $readInt($der, $offset);

        if ($r === null || $s === null) return null;

        return $r . $s;
    }

    protected static function sign(string $signingInput): ?string
    {
        $pemB64 = config('services.vapid.private_key_pem_b64');
        if (!$pemB64) return null;

        $pem = base64_decode($pemB64);
        $key = openssl_pkey_get_private($pem);
        if (!$key) return null;

        $ok = openssl_sign($signingInput, $derSignature, $key, OPENSSL_ALGO_SHA256);
        if (!$ok) return null;

        $raw = static::derSignatureToRaw($derSignature);
        return $raw ? static::base64UrlEncode($raw) : null;
    }

    protected static function buildAuthHeader(string $endpoint): ?string
    {
        $publicKey = config('services.vapid.public_key');
        $subject = config('services.vapid.subject');
        if (!$publicKey) return null;

        $parts = parse_url($endpoint);
        if (!$parts || empty($parts['scheme']) || empty($parts['host'])) return null;
        $aud = $parts['scheme'] . '://' . $parts['host'];

        $header = static::base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
        $payload = static::base64UrlEncode(json_encode([
            'aud' => $aud,
            'exp' => time() + 12 * 3600,
            'sub' => $subject,
        ]));

        $signature = static::sign("{$header}.{$payload}");
        if (!$signature) return null;

        $jwt = "{$header}.{$payload}.{$signature}";

        return "vapid t={$jwt}, k={$publicKey}";
    }

    /**
     * Send an empty-payload push to a single subscription. Returns true on
     * success. Deletes the subscription if the push service reports it's
     * gone (404/410) so we stop wasting time on dead endpoints.
     */
    public static function sendTo(PushSubscription $subscription): bool
    {
        try {
            $authHeader = static::buildAuthHeader($subscription->endpoint);
            if (!$authHeader) return false;

            $ch = curl_init($subscription->endpoint);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => '',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 8,
                CURLOPT_HTTPHEADER => [
                    'TTL: 2419200',
                    'Content-Length: 0',
                    'Authorization: ' . $authHeader,
                ],
            ]);
            curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (in_array($code, [404, 410], true)) {
                $subscription->delete();
            }

            return $code >= 200 && $code < 300;
        } catch (\Throwable $e) {
            Log::warning('Web push send failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send to every subscription belonging to the given user IDs. Best-effort
     * — a push failure must never break the caller's real work (dispatch,
     * emergency creation, etc.).
     */
    public static function sendToUsers(array $userIds): void
    {
        if (empty($userIds) || !config('services.vapid.public_key')) return;

        try {
            $subscriptions = PushSubscription::whereIn('user_id', $userIds)->get();
            foreach ($subscriptions as $subscription) {
                static::sendTo($subscription);
            }
        } catch (\Throwable $e) {
            Log::warning('Web push batch send failed: ' . $e->getMessage());
        }
    }
}
