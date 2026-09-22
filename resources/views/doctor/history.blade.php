@extends('layouts.app')
@section('page-title','Patient Records')
@section('content')
<section class="panel">
    <h2>Find patient records</h2>
    <p class="muted">Enter the patient ID to view details, previous doctors' notes and medical reports.</p>
    <form method="get" action="{{ route('doctor.patients') }}" class="filter-bar">
        <label>Patient ID<input name="patient_id" value="{{ $search }}" maxlength="50" placeholder="Enter patient ID" required></label>
        <button type="submit" class="btn btn-primary">Find patient</button>
    </form>
</section>
@if(!$patient)
    <section class="panel"><x-empty :title="$searched ? 'No patient found with this ID' : 'Search for a patient to view their records'"/></section>
@else
<section class="panel">
    <span class="patient-code">PATIENT ID {{ $patient->patient_code }}</span>
    <h2>{{ $patient->full_name }}</h2>
    <div class="profile-meta">
        <span>{{ $patient->sex }}</span>
        <span>Date of birth: {{ $patient->dob?->format('d M Y') ?: 'Not recorded' }}</span>
        <span>Blood group: {{ $patient->blood_group ?: 'Not recorded' }}</span>
        <span>Phone: {{ $patient->phone ?: 'Not recorded' }}</span>
    </div>
    <p>Address: {{ $patient->address ?: 'Not recorded' }}</p>
</section>
<h2>Previous consultations</h2>
@forelse($consultations as $consultation)
    <article class="panel patient-record-entry">
        <div class="panel-head"><h3>{{ $consultation->doctor?->user?->full_name ?: 'Doctor not recorded' }}</h3><span class="muted">{{ ($consultation->completed_at ?? $consultation->started_at)?->format('d M Y, h:i A') }}</span></div>
        <p><strong>Presenting complaint:</strong> {{ $consultation->chief_complaint ?: 'Not recorded' }}</p>
        <p><strong>Diagnosis:</strong> {{ $consultation->diagnosis_text ?: 'Not recorded' }}</p>
        <p><strong>Clinical notes:</strong> <span class="record-note">{{ $consultation->clinical_notes ?: 'Not recorded' }}</span></p>
        <p><strong>Treatment plan:</strong> <span class="record-note">{{ $consultation->treatment_plan ?: 'Not recorded' }}</span></p>
        @if($consultation->patient_summary)<p><strong>Patient summary:</strong> {{ $consultation->patient_summary }}</p>@endif
        @if($consultation->follow_up_date)<p><strong>Follow-up:</strong> {{ $consultation->follow_up_date->format('d M Y') }}</p>@endif
        <div class="profile-meta">@foreach($consultation->conditions as $condition)<span>{{ $condition->condition_name }}</span>@endforeach</div>
        @foreach($consultation->prescriptions as $prescription)
            <h4>Prescription</h4>
            @foreach($prescription->items as $medicine)
                <p>{{ $medicine->medicine_name }} &middot; {{ $medicine->dosage }} &middot; {{ $medicine->frequency }} &middot; {{ $medicine->duration_days }} day(s)</p>
            @endforeach
            @if($prescription->instructions)<p class="muted">{{ $prescription->instructions }}</p>@endif
        @endforeach
    </article>
@empty
    <section class="panel"><x-empty title="No previous consultations"/></section>
@endforelse
<h2>Medical reports</h2>
@forelse($labOrders as $order)
    <article class="panel patient-record-entry">
        <div class="panel-head"><h3>{{ $order->lab_order_no }}</h3><x-status :value="$order->status"/></div>
        <p class="muted">{{ $order->ordered_at?->format('d M Y, h:i A') }}</p>
        @foreach($order->items as $item)
            <div class="lab-result {{ $item->result?->abnormal_flag ? 'abnormal' : '' }}">
                <h4>{{ $item->test?->test_name ?: 'Laboratory test' }}</h4>
                <p class="record-note">{{ $item->result?->result_text ?: 'Result pending' }}</p>
                @if($item->result?->result_json)<pre class="record-note">{{ json_encode($item->result->result_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>@endif
                @if($item->result?->ai_summary)<p><strong>AI summary:</strong> {{ $item->result->ai_summary }}</p>@endif
                @if($item->result?->attachments?->isNotEmpty())
                    <div class="file-actions">@foreach($item->result->attachments as $attachment)
                        <a class="btn btn-sm btn-secondary" target="_blank" rel="noopener" href="{{ route('documents.view',$attachment) }}">Open {{ $attachment->original_name ?: 'report' }}</a>
                    @endforeach</div>
                @endif
            </div>
        @endforeach
    </article>
@empty
    <section class="panel"><x-empty title="No medical reports recorded"/></section>
@endforelse
@endif
@endsection
