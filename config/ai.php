<?php

return [
    'primary' => env('AI_PRIMARY', 'gemini'),
    'fallback' => env('AI_FALLBACK', 'claude'),
    'local_fallback' => filter_var(env('AI_LOCAL_FALLBACK', true), FILTER_VALIDATE_BOOL),
    'medical_disclaimer' => 'Care guidance is not a diagnosis. A qualified doctor must make clinical decisions. For severe or rapidly worsening symptoms, seek urgent medical care immediately.',
    'lab_result_max_mb' => (int) env('LAB_RESULT_MAX_MB', 8),
];
