<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$checks = [
    'PHP >= 8.2' => version_compare(PHP_VERSION, '8.2.0', '>='),
    'PDO extension' => extension_loaded('pdo'),
    'PDO MySQL extension' => extension_loaded('pdo_mysql'),
    'OpenSSL extension' => extension_loaded('openssl'),
    'Mbstring extension' => extension_loaded('mbstring'),
    'cURL extension' => extension_loaded('curl'),
    'Fileinfo extension' => extension_loaded('fileinfo'),
    'Composer dependencies installed' => is_file($root.'/vendor/autoload.php'),
    '.env exists' => is_file($root.'/.env'),
    'storage writable' => is_writable($root.'/storage'),
    'bootstrap/cache writable' => is_writable($root.'/bootstrap/cache'),
    'Gemini-primary configuration present' => str_contains((string) @file_get_contents($root.'/.env.example'), 'AI_PRIMARY=gemini'),
    'Claude-fallback configuration present' => str_contains((string) @file_get_contents($root.'/.env.example'), 'AI_FALLBACK=claude'),
];

$failed = 0;
echo "MEDICARE Laravel Environment Check\n".str_repeat('=', 38)."\n";
foreach ($checks as $label => $ok) {
    echo sprintf("[%s] %s\n", $ok ? 'PASS' : 'FAIL', $label);
    if (! $ok) $failed++;
}

echo "\n".($failed === 0 ? 'Environment is ready.' : "{$failed} check(s) require attention.")."\n";
exit($failed === 0 ? 0 : 1);
