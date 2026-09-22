@extends('layouts.app')
@section('page-title','Patient Account Links')
@section('content')
<section class="panel"><h2>Verify an existing patient record</h2><p>Check the patient in person against their identification and existing hospital record. A patient ID alone is not proof of identity.</p><a href="{{ route('staff.patients') }}">Open patient directory</a></section>
@forelse($requests as $account)
<section class="panel"><h3>{{ $account->full_name }}</h3><p>Verified email: {{ $account->email }} ? Phone: {{ $account->phone }}</p><p>Requested patient ID: {{ $account->requested_patient_code }}</p>
<form method="post" action="{{ route('staff.patient-links.store',$account) }}">@csrf
@php($record=$records->get($account->requested_patient_code))
@if($record)<div class="summary-box"><strong>Existing hospital record: {{ $record->full_name }}</strong><p>Date of birth: {{ $record->dob?->format('d M Y') ?: 'Not recorded' }} · Phone: {{ $record->phone ?: 'Not recorded' }}</p><p>NIC/passport: {{ $record->nic_passport ?: 'Not recorded' }}</p></div>@else<p>No record matches the submitted ID. Verify the correct ID with the patient before proceeding.</p>@endif
<label>Verified patient ID<input name="patient_code" value="{{ $account->requested_patient_code }}" maxlength="4" required></label>
<label>Verification note<textarea name="verification_note" minlength="10" maxlength="500" required></textarea></label>
<label class="check-row"><input type="checkbox" name="identity_verified" value="1" required>I checked the person's identity against the existing hospital record.</label>
<button class="btn btn-primary">Link existing patient record</button>
</form></section>
@empty<section class="panel"><x-empty title="No account links awaiting verification"/></section>@endforelse
@endsection
