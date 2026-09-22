@extends('layouts.app')
@section('page-title','Walk-in Booking')
@section('content')
<section class="panel">
<h2>Find patient and doctor</h2>
<p class="muted">Search existing patients first to keep their history under one ID.</p>
<form method="get" action="{{ route('staff.walk-in') }}#walk-in-results" class="filter-bar">
<label>Patient ID, name, phone or NIC<input name="q" value="{{ request('q') }}" maxlength="100"></label>
<label>Doctor<select name="doctor_id" required><option value="">Select doctor</option>@foreach($doctors as $doctor)<option value="{{ $doctor->doctor_id }}" @selected(request('doctor_id')==$doctor->doctor_id)>{{ $doctor->user->full_name }} &middot; Rs. {{ number_format($doctor->consultation_fee,2) }}</option>@endforeach</select></label>
<label>Date<input type="date" name="date" value="{{ request('date',today()->toDateString()) }}" min="{{ today()->toDateString() }}" required></label>
<button type="submit" class="btn btn-secondary">Search</button>
</form></section>
<section class="panel" id="walk-in-results" style="scroll-margin-top:110px">
<h2>Patient, appointment and payment</h2>@if(request()->has('doctor_id'))
<div class="summary-box" role="status">
    <p><strong>{{ $patients->count() }} matching patient(s) found.</strong>
    @if($patients->count()===1){{ $patients->first()->full_name }} (ID {{ $patients->first()->patient_code }}) is selected below.@endif</p>
    @if($slots->isEmpty())<p>No bookable slots for this doctor on {{ request('date') }}. Choose another date or doctor. Booking closes when a session starts.</p>
    @else<p>{{ $slots->count() }} available slots. Select a time below to continue.</p>@endif
</div>
@endif
<form method="post" action="{{ route('staff.walk-in.store') }}">@csrf
<label>Existing patient<select name="patient_id" id="walk-in-patient"><option value="">Create a new walk-in patient</option>@foreach($patients as $patient)<option value="{{ $patient->patient_id }}" @selected(old('patient_id',$patients->count()===1 ? $patients->first()->patient_id : '')==$patient->patient_id)>{{ $patient->patient_code }} &middot; {{ $patient->full_name }} &middot; {{ $patient->phone }}</option>@endforeach</select></label>
@if(request('q') && $patients->isEmpty())<p class="muted">No matching patient found. Check the ID or search another detail before creating a new record.</p>@endif
<fieldset id="walk-in-new-patient"><legend>New patient details &mdash; fill only when creating a new patient</legend>
<div class="form-grid">
<label>First name<input name="first_name" maxlength="70" value="{{ old('first_name') }}"></label>
<label>Last name<input name="last_name" maxlength="70" value="{{ old('last_name') }}"></label>
<label>Date of birth<input type="date" name="dob" value="{{ old('dob') }}" max="{{ today()->toDateString() }}"></label>
<label>Sex<select name="sex">@foreach(['UNKNOWN','MALE','FEMALE','OTHER'] as $sex)<option @selected(old('sex')==$sex)>{{ $sex }}</option>@endforeach</select></label>
<label>Phone (optional)<input name="phone" value="{{ old('phone') }}" maxlength="10"></label>
<label>NIC / passport (optional)<input name="nic_passport" value="{{ old('nic_passport') }}" maxlength="30"></label>
</div></fieldset>
<h3>Appointment slot</h3>
<div class="slot-grid">@forelse($slots as $slot)<label class="slot-option"><input type="radio" name="slot_id" value="{{ $slot->slot_id }}" required><span>{{ $slot->slot_start->format('d M Y h:i A') }}<small>Room {{ $slot->schedule->room_no ?: 'TBA' }}</small></span></label>@empty<p>Select a doctor and date with available slots.</p>@endforelse</div>
<h3>Payment at hospital</h3>
<div class="form-grid"><label>Payment method<select name="payment_method"><option value="CASH">Cash</option><option value="CARD">Card &mdash; hospital terminal</option></select></label>
<label>Terminal reference (optional)<input name="transaction_reference" maxlength="100"></label></div>
<p>For card payments, complete payment on the hospital card machine first. Do not enter card numbers or CVV here.</p>
<label class="check-row"><input type="checkbox" name="payment_received" value="1" required>I have received the full consultation fee shown above.</label>
<div class="form-actions" style="margin-top:20px"><button class="btn btn-primary" @disabled($slots->isEmpty())>Confirm booking &amp; create receipt</button></div>
</form></section>
@endsection
@push('scripts')
<script>
(() => {
    const patient = document.getElementById('walk-in-patient');
    const fields = document.getElementById('walk-in-new-patient');
    const update = () => {
        fields.hidden = Boolean(patient.value);
        fields.disabled = Boolean(patient.value);
    };
    patient.addEventListener('change', update);
    update();
})();
</script>
@endpush