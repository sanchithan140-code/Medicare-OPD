@extends('layouts.app')
@section('title','Medical History — MEDICARE')
@section('page-title','Digital Medical History')
@section('content')
<div class="timeline">
@forelse($consultations as $consultation)
<article class="timeline-item"><div class="timeline-dot"></div><div class="panel"><div class="panel-head"><div><span class="kicker">{{ $consultation->completed_at?->format('d M Y') }}</span><h2>{{ $consultation->patient->full_name }}</h2><p class="muted">{{ $consultation->doctor->user->full_name }}</p></div><span class="patient-code">{{ $consultation->patient->patient_code }}</span></div>
<div class="detail-grid"><div><small>Diagnosis</small><p>{{ $consultation->diagnosis_text }}</p></div><div><small>Treatment plan</small><p>{{ $consultation->treatment_plan }}</p></div><div><small>Patient-visible summary</small><p>{{ $consultation->patient_summary ?: 'Not provided' }}</p></div><div><small>Follow-up</small><p>{{ $consultation->follow_up_date?->format('d M Y') ?: 'As needed' }}</p></div></div>
@if($consultation->conditions->isNotEmpty())<div class="profile-meta">@foreach($consultation->conditions as $condition)<span>{{ $condition->condition_name }}</span>@endforeach</div>@endif
@if($consultation->prescriptions->isNotEmpty())<hr class="divider"><h3>Prescription</h3>@foreach($consultation->prescriptions as $prescription)<p>{{ $prescription->instructions }}</p><div class="table-scroll"><table><thead><tr><th>Medicine</th><th>Dosage</th><th>Frequency</th><th>Duration</th></tr></thead><tbody>@foreach($prescription->items as $item)<tr><td>{{ $item->medicine_name }}</td><td>{{ $item->dosage }}</td><td>{{ $item->frequency }}</td><td>{{ $item->duration_days ? $item->duration_days.' days' : 'As directed' }}</td></tr>@endforeach</tbody></table></div>@endforeach @endif
</div></article>
@empty<x-empty title="No completed consultations" message="Completed doctor consultations will appear as a permanent history timeline."/>@endforelse
</div>{{ $consultations->links() }}
@endsection
