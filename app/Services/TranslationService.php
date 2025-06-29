<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    public static function translate(string $text, string $from, string $to): string
    {
        if ($from === $to) return $text;

        try {
            $response = Http::asForm()->withHeaders([
                'Authorization' => 'DeepL-Auth-Key ' . config('services.deepl.api_key'),
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json',
            ])->post(env('DEEPL_API_URL'), [
                'text' => $text,
                'source_lang' => strtoupper($from),
                'target_lang' => strtoupper($to),
            ]);

            Log::info('[DEEPL RESPONSE] ' . $response->body());
            if ($response->successful()) {
                return $response->json()['translations'][0]['text'] ?? $text;
            }

            Log::error('[DEEPL ERROR] ' . $response->body());
        } catch (\Throwable $e) {
            Log::error('[DEEPL EXCEPTION] ' . $e->getMessage());
        }

        return $text . ' [non traduit]';
    }
}
