<?php

return [
    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-3.6-flash'),
        'timeout' => (int) env('GEMINI_TIMEOUT', 35),
    ],
    'anthropic' => [
        'key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-4-20250514'),
        'timeout' => (int) env('ANTHROPIC_TIMEOUT', 35),
    ],
];
