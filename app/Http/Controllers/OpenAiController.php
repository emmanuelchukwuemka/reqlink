<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OpenAiController extends Controller
{
    public function chat(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);
        $message = $validated['message'];
        $apiKey = config('services.openai.key');

        if (!$apiKey) {
            return response()->json(['error' => 'API Key not configured'], 500);
        }

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(25)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are ResQLink Medical AI, an empathetic, calm, and professional digital first-responder. You answer any medical question the user asks — symptoms, conditions, medication dosages, first aid, mental health, pediatric and maternal care — with clear, accurate, actionable guidance. Keep answers concise and practical. Always start with a brief medical disclaimer. If the situation sounds critical or life-threatening, tell the user to use the Red SOS button immediately.'],
                    ['role' => 'user', 'content' => $message],
                ],
                'temperature' => 0.7,
                'max_tokens' => 600,
            ]);

            if ($response->failed()) {
                \Illuminate\Support\Facades\Log::error('OpenAI API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return response()->json(['error' => 'OpenAI API request failed: ' . $response->status()], 500);
            }

            return response()->json($response->json());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('OpenAI Connection Exception: ' . $e->getMessage());
            return response()->json(['error' => 'Connection Exception: ' . $e->getMessage()], 500);
        }
    }
}
