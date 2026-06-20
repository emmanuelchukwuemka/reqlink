<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OpenAiController extends Controller
{
    public function chat(Request $request)
    {
        $message = $request->input('message');
        $apiKey = env('OPENAI_API_KEY');

        if (!$apiKey) {
            return response()->json(['error' => 'API Key not configured'], 500);
        }

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are ResQLink Medical AI, an empathetic, calm, and professional digital first-responder. Your goal is to provide clear, actionable medical advice and first-aid instructions. Always start with a medical disclaimer. If the situation is critical, tell the user to use the Red SOS button immediately.'],
                    ['role' => 'user', 'content' => $message],
                ],
                'temperature' => 0.7,
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
