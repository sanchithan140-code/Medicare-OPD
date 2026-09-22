<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'ai.primary' => 'gemini']);

$provider = new class extends App\Services\AI\GeminiProvider {
    public array $response = [];
    public function available(): bool { return true; }
    public function generateJson(string $system, string $prompt, ?array $attachment = null): array
    {
        if (!str_contains($system, 'medical and healthcare-only') || !str_contains($system, 'untrusted data')) {
            throw new RuntimeException('Missing medical scope instructions.');
        }
        return $this->response;
    }
};
$local = new App\Services\AI\LocalClinicalRoutingProvider;
$gateway = new App\Services\AI\AiGateway($provider, new App\Services\AI\ClaudeProvider, $local);
$cases = [
    [['in_scope' => true, 'reply' => 'A medical explanation.'], false],
    [['in_scope' => false, 'reply' => 'Unrelated answer must be discarded.'], true],
    [['reply' => 'Unclassified answer must be discarded.'], true],
    [['in_scope' => 'true', 'reply' => 'Invalid classification.'], true],
    [['in_scope' => true, 'reply' => ['invalid']], true],
    [['in_scope' => true, 'reply' => '   '], true],
];
foreach ($cases as [$response, $ignored]) {
    $provider->response = $response;
    $result = $gateway->chat([['role' => 'user', 'content' => 'What is hypertension?']]);
    if ($result['ignored'] !== $ignored || ($ignored && $result['reply'] !== '')) {
        throw new RuntimeException('Scope enforcement failed.');
    }
}
$result = $local->generateJson('', json_encode(['conversation' => [['role' => 'user', 'content' => 'Tell me a joke']]]));
if ($result !== ['in_scope' => false, 'reply' => '']) {
    throw new RuntimeException('Local fallback must remain silent.');
}
echo "PASS: medical responses, ignored responses, malformed output, and silent local fallback.\n";
