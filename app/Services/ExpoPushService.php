<?php

namespace App\Services;

use App\Models\DeviceToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends push notifications to the React Native app via Expo's push service.
 * This is deliberately separate from WebPushService (Web Push/VAPID, browser
 * push services only) — Expo push tokens reach native iOS/Android devices
 * through Expo's own relay, no FCM/APNs credentials needed on this backend.
 */
class ExpoPushService
{
    protected static string $endpoint = 'https://exp.host/--/api/v2/push/send';

    /**
     * Send the same notification to every registered device of the given
     * user IDs. Best-effort — a push failure must never break the caller's
     * real work (dispatch, emergency creation, etc.).
     */
    public static function sendToUsers(array $userIds, string $title, string $body, array $data = []): void
    {
        if (empty($userIds)) {
            return;
        }

        try {
            $tokens = DeviceToken::whereIn('user_id', $userIds)->get();
            if ($tokens->isEmpty()) {
                return;
            }

            foreach ($tokens->chunk(100) as $chunk) {
                $messages = $chunk->map(fn (DeviceToken $token) => [
                    'to' => $token->expo_push_token,
                    'title' => $title,
                    'body' => $body,
                    'data' => $data,
                    'sound' => 'default',
                    'priority' => 'high',
                ])->values()->all();

                $response = Http::timeout(8)->post(static::$endpoint, $messages);
                static::pruneDeadTokens($chunk->values(), $response->json('data', []));
            }
        } catch (\Throwable $e) {
            Log::warning('Expo push batch send failed: ' . $e->getMessage());
        }
    }

    /**
     * Tickets are returned in the same order as the messages that were sent,
     * so we zip them back against the token batch to know which rows to drop.
     */
    protected static function pruneDeadTokens($tokens, array $tickets): void
    {
        foreach ($tickets as $index => $ticket) {
            $token = $tokens[$index] ?? null;
            if (!$token) {
                continue;
            }

            $isDead = ($ticket['status'] ?? null) === 'error'
                && in_array($ticket['details']['error'] ?? null, ['DeviceNotRegistered', 'InvalidCredentials'], true);

            if ($isDead) {
                $token->delete();
            }
        }
    }
}
