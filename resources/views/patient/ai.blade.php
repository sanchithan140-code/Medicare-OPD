@extends('layouts.app') @section('page-title','Care Guide') @section('content')<div class="grid-ai">
<section class="panel ai-form-card">
<div class="ai-brand">
<span>✦</span>
<div>
<span class="kicker">YOUR NEXT STEP STARTS HERE</span>
<h2>Find care that fits your needs</h2>
</div>
</div>
<p class="muted">Share your main symptoms clearly. Your care guide suggests a specialty and priority level to help you move forward with confidence.</p>
<form method="post" action="{{ route('patient.ai.recommend') }}">@csrf<label>Patient profile<select name="patient_id">
<option value="">General guidance</option>@foreach($patients as $p)<option value="{{ $p->patient_id }}">{{ $p->full_name }} ({{ $p->patient_code }})</option>@endforeach</select>
</label>
<label>Symptoms<textarea name="symptoms" rows="10" minlength="10" placeholder="Example: fever for two days, cough, sore throat, age and relevant history..." required>{{ old('symptoms') }}</textarea>
</label>
<label class="check-row warning-check">
<input type="checkbox" name="disclaimer" value="1" required> I understand that this is routing guidance only and not a diagnosis or emergency service.</label>
<button class="btn btn-primary btn-block">Find my next step</button>
</form>
<div class="urgent-note">
<strong>Emergency warning</strong>
<p>For severe chest pain, major breathing difficulty, unconsciousness or another emergency, contact emergency services or go directly to the hospital.</p>
</div>
</section>
<section class="panel ai-result-card">@if($result)@php
    $urgency=$result['urgency'] ?? 'ROUTINE';
    $priorityLabel=match($urgency) {
        'EMERGENCY'=>'Get medical help now',
        'URGENT'=>'Prompt medical care recommended',
        'SOON'=>'Arrange a consultation soon',
        default=>'Arrange a consultation',
    };
    $nextStep=match($urgency) {
        'EMERGENCY'=>'Contact emergency services or go to the nearest emergency department now. Do not wait for an online appointment.',
        'URGENT'=>'Seek prompt in-person medical care. Contact the hospital directly if you cannot get a prompt appointment.',
        default=>$result['next_step'] ?? 'Arrange a consultation to discuss your symptoms and the next steps with a doctor.',
    };
@endphp
<div class="care-result-heading"><span class="kicker">YOUR CARE GUIDE</span><span class="urgency urgency-{{ strtolower($urgency) }}">{{ $priorityLabel }}</span></div><h2>Suggested specialty: {{ $result['specialty'] ?? 'General Medicine' }}</h2>
<p>{{ $result['summary'] ?? ($result['reason'] ?? '') }}</p>
<div class="care-next-step"><h3>What to do next</h3><p>{{ $nextStep }}</p></div>
@if(!empty($result['red_flags']))
<div class="care-watch-signs"><h3>When to get help right away</h3>
<p class="muted">If you notice any of these signs, seek urgent medical help:</p>
<ul class="clean-list">@foreach($result['red_flags'] as $flag)<li>{{ $flag }}</li>@endforeach</ul></div>
@endif
<p class="care-guidance-note">This guide helps you choose care; it does not diagnose a condition. A doctor can assess your symptoms and explain the treatment you may need.</p><hr class="divider">
@if($urgency !== 'EMERGENCY')<h3>Doctors in this specialty</h3>
<div class="mini-doctors">@forelse($doctors as $d)<a href="{{ route('patient.appointments.book',['doctor_id'=>$d->doctor_id,'recommendation_id'=>$result['recommendation_id'] ?? null]) }}">
<div class="doctor-avatar">{{ strtoupper(substr($d->user->full_name,0,1)) }}</div>
<div>
<strong>{{ $d->user->full_name }}</strong>
<small>{{ $d->specialty->specialty_name }} · Rs. {{ number_format($d->consultation_fee,2) }}</small>
</div>
<b>Book →</b>
</a>@empty<p class="muted">No active doctor is currently linked to the recommended specialty.</p>@endforelse</div>@endif @else<div class="center-card">
<img src="{{ asset('assets/images/illustrations/empty-state.svg') }}" width="180" alt="">
<h2>Your next step will appear here</h2>
<p>Share enough symptom detail to receive helpful specialty guidance. Taking action today is a step toward feeling better.</p>
</div>@endif</section>
</div>@endsection
