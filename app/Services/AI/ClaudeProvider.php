<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ClaudeProvider implements AiProvider
{
    public function name(): string { return 'claude'; }
    public function model(): string { return (string) config('services.anthropic.model'); }
    public function available(): bool { return filled(config('services.anthropic.key')); }

    public function generateJson(string $system, string $prompt, ?array $attachment = null): array
    {
        if (! $this->available()) {
            throw new RuntimeException('Claude API key is not configured.');
        }

        $content = [['type' => 'text', 'text' => $prompt]];
        if ($attachment) {
            if ($attachment['mime_type'] === 'application/pdf') {
                $content[] = [
                    'type' => 'document',
                    'source' => [
                        'type' => 'base64',
                        'media_type' => 'application/pdf',
                        'data' => $attachment['data'],
                    ],
                ];
            } elseif (str_starts_with($attachment['mime_type'], 'image/')) {
                $content[] = [
                    'type' => 'image',
                    'source' => [
                        'type' => 'base64',
                        'media_type' => $attachment['mime_type'],
                        'data' => $attachment['data'],
                    ],
                ];
            }
        }

        $response = Http::timeout((int) config('services.anthropic.timeout', 35))
            ->retry(2, 500)
            ->withHeaders([
                'x-api-key' => config('services.anthropic.key'),
                'anthropic-version' => '2023-06-01',
            ])
            ->acceptJson()
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => $this->model(),
                'max_tokens' => 1800,
                'system' => $system,
                'messages' => [['role' => 'user', 'content' => $content]],
            ]);

        $response->throw();

        $text = collect($response->json('content', []))
            ->where('type', 'text')
            ->pluck('text')
            ->implode("\n");

        if (trim($text) === '') {
            throw new RuntimeException('Claude returned an empty response.');
        }

        $clean = trim(preg_replace('/^```(?:json)?|```$/m', '', $text) ?? $text);
        $data = json_decode($clean, true);
        if (! is_array($data)) {
            throw new RuntimeException('Claude response was not valid JSON.');
        }

        return $data;
    }
}
