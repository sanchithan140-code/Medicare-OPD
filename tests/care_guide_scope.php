<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'ai.primary' => 'gemini']);
$local = new App\Services\AI\LocalClinicalRoutingProvider;
foreach (['I HAVE A FREE TIME', 'I have free time', 'Tell me a joke', 'This year I want to learn programming', 'Find a movie for my child'] as $input) {
    $result = $local->generateJson('', json_encode(['symptoms' => $input]));
    if ($result !== ['in_scope' => false]) throw new RuntimeException('Unrelated input was routed: '.$input);
}
foreach (['I have fever and cough', 'I have severe chest pain', 'I cannot breathe'] as $input) {
    $result = $local->generateJson('', json_encode(['symptoms' => $input]));
    if ($result['in_scope'] !== true) throw new RuntimeException('Clinical concern rejected: '.$input);
    if ($input !== 'I have fever and cough' && $result['urgency'] !== 'EMERGENCY') throw new RuntimeException('Emergency routing lost.');
}
$provider = new class extends App\Services\AI\GeminiProvider {
    public array $response = [];
    public function available(): bool { return true; }
    public function generateJson(string $system, string $prompt, ?array $attachment = null): array { return $this->response; }
};
$gateway = new App\Services\AI\AiGateway($provider, new App\Services\AI\ClaudeProvider, $local);
foreach ([[], ['in_scope' => false, 'specialty' => 'Cardiology'], ['in_scope' => 'true']] as $response) {
    $provider->response = $response;
    if ($gateway->recommendSpecialty('I have a free time', ['General Medicine']) !== ['in_scope' => false]) {
        throw new RuntimeException('Gateway must discard unclassified or unrelated recommendations.');
    }
}
$provider->response = ['in_scope' => true, 'specialty' => 'General Medicine', 'urgency' => 'ROUTINE'];
if ($gateway->recommendSpecialty('I have fever and cough', ['General Medicine'])['in_scope'] !== true) {
    throw new RuntimeException('Gateway rejected classified medical input.');
}
echo "PASS: unrelated input, substring false positives, medical concerns, emergency routing, and gateway scope enforcement.\n";
