<?php

namespace App\Http\Controllers;

use App\Models\AiRecommendation;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Specialty;
use App\Services\AI\AiGateway;
use App\Services\AuditService;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function __construct(private readonly AiGateway $ai, private readonly AuditService $audit) {}

    public function show(Request $request)
    {
        return view('patient.ai', [
            'patients' => $request->user()->patients()->where('active', true)->get(),
            'result' => session('ai_result'),
            'doctors' => session('ai_doctors', collect()),
        ]);
    }

    public function recommend(Request $request)
    {
        $data = $request->validate([
            'patient_id' => ['nullable','integer'],
            'symptoms' => ['required','string','min:10','max:3000'],
            'disclaimer' => ['accepted'],
        ]);

        $patient = null;
        if (! empty($data['patient_id'])) {
            $patient = Patient::where('patient_id', $data['patient_id'])
                ->where('account_user_id', $request->user()->user_id)
                ->firstOrFail();
        }

        $specialties = Specialty::where('active', true)->orderBy('specialty_name')->pluck('specialty_name')->all();
        $result = $this->ai->recommendSpecialty($data['symptoms'], $specialties);
        if (($result['in_scope'] ?? false) !== true) {
            $request->session()->forget(['ai_result', 'ai_doctors']);
            return redirect()->route('patient.ai')
                ->withInput($request->only(['patient_id', 'symptoms']))
                ->withErrors(['symptoms' => 'Please describe a medical symptom or health concern so the Care Guide can help you find appropriate care.']);
        }
        $specialty = Specialty::where('specialty_name', $result['specialty'])->first();

        $recommendation = AiRecommendation::create([
            'user_id' => $request->user()->user_id,
            'patient_id' => $patient?->patient_id,
            'symptom_text' => $data['symptoms'],
            'provider' => $result['_provider'],
            'model' => $result['_model'],
            'recommended_specialty_id' => $specialty?->specialty_id,
            'urgency' => $result['urgency'],
            'confidence' => $result['confidence'],
            'response_json' => $result,
            'disclaimer_accepted' => true,
        ]);

        $doctors = $specialty
            ? Doctor::with('user','specialty')
                ->where('specialty_id', $specialty->specialty_id)
                ->where('active', true)
                ->orderBy('consultation_fee')
                ->get()
            : collect();

        $result['recommendation_id'] = $recommendation->recommendation_id;
        $this->audit->record('ai.recommendation.created', $recommendation, [], [
            'provider' => $result['_provider'],
            'specialty' => $result['specialty'],
        ]);

        return redirect()->route('patient.ai')->with([
            'ai_result' => $result,
            'ai_doctors' => $doctors,
            'success' => 'Your care guidance is ready. Review the safety note before booking.',
        ]);
    }
}
