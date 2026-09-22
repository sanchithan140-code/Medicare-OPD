<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiProvider implements AiProvider
{
    public function name(): string { return 'gemini'; }
    public function model(): string { return (string) config('services.gemini.model'); }
    public function available(): bool { return filled(config('services.gemini.key')); }

    public function generateJson(string $system, string $prompt, ?array $attachment = null): array
    {
        if (! $this->available()) {
            throw new RuntimeException('Gemini API key is not configured.');
        }

        $parts = [['text' => $prompt]];
        if ($attachment) {
            $parts[] = [
                'inline_data' => [
                    'mime_type' => $attachment['mime_type'],
                    'data' => $attachment['data'],
                ],
            ];
        }

        $response = Http::timeout((int) config('services.gemini.timeout', 35))
            ->retry(2, 500)
            ->withHeaders(['x-goog-api-key' => config('services.gemini.key')])
            ->acceptJson()
            ->post(
                'https://generativelanguage.googleapis.com/v1beta/models/'.rawurlencode($this->model()).':generateContent',
                [
                    'system_instruction' => ['parts' => [['text' => $system]]],
                    'contents' => [['role' => 'user', 'parts' => $parts]],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'maxOutputTokens' => 1800,
                    ],
                ]
            );

        $response->throw();

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');
        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('Gemini returned an empty response.');
        }

        return $this->decodeJson($text);
    }

    private function decodeJson(string $text): array
    {
        $clean = trim(preg_replace('/^```(?:json)?|```$/m', '', $text) ?? $text);
        $data = json_decode($clean, true);

        if (! is_array($data)) {
            throw new RuntimeException('Gemini response was not valid JSON.');
        }

        return $data;
    }
}
