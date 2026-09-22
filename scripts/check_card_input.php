<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Services\CardInput;
use Illuminate\Support\Carbon;
Carbon::setTestNow(Carbon::parse('2026-09-05'));
$cases = [
    [CardInput::validNumber('4242424242424242'), true],
    [CardInput::validNumber('378282246310005'), false],
    [CardInput::validNumber('4444444444444444444'), false],
    [CardInput::validNumber('0000000000000000'), true],
    [CardInput::validNumber('4242424242424241'), true],
    [CardInput::validNumber('4242'), false],
    [CardInput::validExpiry('09/26'), true],
    [CardInput::validExpiry('12/28'), true],
    [CardInput::validExpiry('08/26'), false],
    [CardInput::validExpiry('13/28'), false],
    [CardInput::validExpiry('00/28'), false],
    [CardInput::validExpiry('9/26'), false],
];
foreach ($cases as $index => [$actual, $expected]) {
    if ($actual !== $expected) throw new RuntimeException("Failed case {$index}");
}
Carbon::setTestNow();
echo "PASS: 12 card number and expiry validation cases.".PHP_EOL;
