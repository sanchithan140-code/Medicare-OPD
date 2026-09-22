<?php

namespace App\Services\AI;

interface AiProvider
{
    public function name(): string;
    public function model(): string;
    public function available(): bool;

    /**
     * @param array{mime_type:string,data:string}|null $attachment Base64-encoded optional document/image.
     */
    public function generateJson(string $system, string $prompt, ?array $attachment = null): array;
}
