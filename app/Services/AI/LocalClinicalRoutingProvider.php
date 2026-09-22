<?php

namespace App\Services\AI;

class LocalClinicalRoutingProvider implements AiProvider
{
    public function name(): string { return 'local_rules'; }
    public function model(): string { return 'medicare-safe-fallback-v2'; }
    public function available(): bool { return true; }

    public function generateJson(string $system, string $prompt, ?array $attachment = null): array
    {
        $payload = json_decode($prompt, true);
        $task = is_array($payload) ? mb_strtolower((string) ($payload['task'] ?? '')) : '';

        // Local specialty rules cannot reliably classify conversational relevance.
        if (isset($payload['conversation'])) {
            return ['in_scope' => false, 'reply' => ''];
        }

        if (str_contains($task, 'laboratory')) {
            return $this->labSummary((string) ($payload['test_name'] ?? 'Laboratory test'), (string) ($payload['result_text'] ?? ''));
        }

        if (str_contains($task, 'no-show')) {
            return $this->noShow((array) ($payload['features'] ?? []));
        }

        if (str_contains($task, 'disease trend') || str_contains($task, 'aggregate')) {
            return $this->trend((array) ($payload['trends'] ?? []));
        }

        return $this->specialtyRouting((string) ($payload['symptoms'] ?? $prompt), (array) ($payload['allowed_specialties'] ?? []));
    }

    private function specialtyRouting(string $symptoms, array $allowedSpecialties): array
    {
        $text = mb_strtolower($symptoms);
        // Without a model, only route recognizable clinical concerns. Never default
        // unrelated text to General Medicine or match "ear" inside "year".
        if (! preg_match('/\b(?:pain|discomfort|palpitations?|high blood pressure|rash|itch(?:ing)?|acne|eczema|sore throat|sinusitis|tonsillitis|fracture|blurred vision|red eye|pregnan(?:t|cy)|menstrual|fever|cough(?:ing)?|headache|fatigue|vomit(?:ing)?|diarrh(?:ea|oea)|cannot breathe|can\x27t breathe|unconscious|bleeding|stroke|swelling|dizziness|nausea|diabetes|asthma|depression|anxiety)\b/u', $text)) {
            return ['in_scope' => false];
        }
        $rules = [
            'Cardiology' => ['chest pain','chest discomfort','palpitation','heart','high blood pressure'],
            'Dermatology' => ['rash','skin','itch','acne','eczema'],
            'ENT' => ['ear','nose','throat','sinus','tonsil'],
            'Orthopaedics' => ['joint','bone','back pain','knee','shoulder','fracture'],
            'Ophthalmology' => ['eye','vision','red eye'],
            'Paediatrics' => ['child','baby','infant','pediatric'],
            'Gynaecology' => ['pregnancy','menstrual','period pain','gynaec'],
            'General Medicine' => ['fever','cough','headache','fatigue','vomit','diarrhea'],
        ];

        $specialty = in_array('General Medicine', $allowedSpecialties, true)
            ? 'General Medicine'
            : ((string) ($allowedSpecialties[0] ?? 'General Medicine'));

        foreach ($rules as $name => $keywords) {
            if ($allowedSpecialties && ! in_array($name, $allowedSpecialties, true)) {
                continue;
            }
            foreach ($keywords as $keyword) {
                if (preg_match('/\b'.preg_quote($keyword, '/').'\b/u', $text)) {
                    $specialty = $name;
                    break 2;
                }
            }
        }

        $urgency = 'ROUTINE';
        foreach (['severe chest pain','cannot breathe','unconscious','heavy bleeding','stroke'] as $term) {
            if (str_contains($text, $term)) {
                $urgency = 'EMERGENCY';
                break;
            }
        }

        return [
            'in_scope' => true,
            'specialty' => $specialty,
            'urgency' => $urgency,
            'confidence' => 0.55,
            'summary' => 'A safe local fallback matched the symptom description to a suitable starting specialty.',
            'red_flags' => $urgency === 'EMERGENCY'
                ? ['The description may include an urgent warning sign. Seek immediate in-person medical care.']
                : [],
            'questions_for_doctor' => [
                'When did the symptoms begin?',
                'What makes them better or worse?',
                'Are there existing medical conditions or medicines?',
            ],
            'disclaimer' => config('ai.medical_disclaimer'),
        ];
    }

    private function labSummary(string $testName, string $resultText): array
    {
        $lower = mb_strtolower($resultText);
        $abnormal = preg_match('/\b(high|low|abnormal|positive|critical|outside|elevated|reduced)\b/u', $lower) === 1;
        $summary = trim($resultText) !== ''
            ? "The {$testName} result has been recorded. The original values and reference ranges must be reviewed by the requesting doctor."
            : "A {$testName} report document was uploaded. The requesting doctor must review the original report.";

        return [
            'summary' => $summary,
            'abnormal_flag' => $abnormal,
            'flagged_items' => $abnormal ? ['The entered result includes wording that may indicate an out-of-range or positive value.'] : [],
            'doctor_note' => 'Review the original values, units and laboratory reference ranges before making a clinical decision.',
            'patient_note' => 'This summary is not a diagnosis. Discuss the report with a qualified doctor.',
        ];
    }

    private function noShow(array $features): array
    {
        $score = (float) ($features['calculated_risk_score'] ?? 0);
        $label = $score >= 0.6 ? 'HIGH' : ($score >= 0.3 ? 'MEDIUM' : 'LOW');
        $noShows = (int) ($features['previous_no_shows'] ?? 0);
        $cancellations = (int) ($features['previous_cancellations'] ?? 0);

        return [
            'risk_label' => $label,
            'explanation' => "Operational history includes {$noShows} previous no-show(s) and {$cancellations} cancellation(s).",
            'recommended_reminders' => $label === 'HIGH'
                ? ['48 hours before','24 hours before','4 hours before']
                : ['24 hours before','4 hours before'],
        ];
    }

    private function trend(array $rows): array
    {
        $count = count($rows);
        $top = collect($rows)->sortByDesc(fn ($row) => (int) data_get($row, 'case_count', 0))->first();
        $topName = (string) data_get($top, 'condition_name', 'the leading recorded condition');
        $topCount = (int) data_get($top, 'case_count', 0);

        return [
            'summary' => $count > 0
                ? "The weekly review contains {$count} diagnosed condition group(s). {$topName} has the highest recorded count ({$topCount})."
                : 'No completed consultation trend rows were available for the selected week.',
            'management_actions' => ['Review staffing and laboratory workload.', 'Confirm unusual changes against clinical records before issuing any alert.'],
            'patient_widget_note' => 'Weekly figures are aggregate service information and do not confirm an outbreak.',
        ];
    }
}
