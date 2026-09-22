@extends('layouts.app')
@section('page-title','Book an Appointment')
@section('content')
<div class="dashboard-grid align-start">
    <section class="panel">
        <div class="panel-head"><div><span class="kicker">STEP 1</span><h2>Choose a doctor</h2></div></div>
        <form method="get" action="{{ route('patient.appointments.book') }}">
            <div class="form-grid">
                <label>Doctor
                    <select name="doctor_id" required>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->doctor_id }}" @selected($selectedDoctor?->doctor_id === $doctor->doctor_id)>
                                {{ $doctor->user->full_name }} — {{ $doctor->specialty->specialty_name }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label>Availability period
                    <input value="{{ $rangeStart->format('d M Y') }} – {{ $rangeEnd->format('d M Y') }}" readonly>
                </label>
            </div>
            @if($recommendationId)
                <input type="hidden" name="recommendation_id" value="{{ $recommendationId }}">
            @endif
            <button class="btn btn-secondary">Show available slots</button>
        </form>

        @if($selectedDoctor)
            <div class="doctor-summary">
                <div class="doctor-avatar">{{ strtoupper(substr($selectedDoctor->user->full_name, 0, 1)) }}</div>
                <div>
                    <h3>{{ $selectedDoctor->user->full_name }}</h3>
                    <p>{{ $selectedDoctor->specialty->specialty_name }} · Rs. {{ number_format($selectedDoctor->consultation_fee, 2) }}</p>
                </div>
            </div>
            <div class="availability-days">
                @forelse($availability as $availableDate => $daySlots)
                    <a class="availability-day {{ $selectedDate === $availableDate ? 'selected' : '' }}"
                       href="{{ route('patient.appointments.book', array_filter(['doctor_id' => $selectedDoctor->doctor_id, 'date' => $availableDate, 'recommendation_id' => $recommendationId])) }}#booking-slots">
                        <span class="date-tile">
                            <strong>{{ $daySlots->first()->slot_start->format('d') }}</strong>
                            <span>{{ $daySlots->first()->slot_start->format('M') }}</span>
                        </span>
                        <span>
                            <strong>{{ $daySlots->first()->slot_start->format('l, d M Y') }}</strong>
                            <small>{{ $daySlots->first()->slot_start->format('h:i A') }} – {{ $daySlots->last()->slot_start->format('h:i A') }} · {{ $daySlots->count() }} slots</small>
                        </span>
                        <b>Choose →</b>
                    </a>
                @empty
                    <p class="muted">No available dates for this doctor during the next 20 days.</p>
                @endforelse
            </div>
        @endif
    </section>

    @if($selectedDate)
    <section class="panel" id="booking-slots">
        <div class="panel-head"><div><span class="kicker">STEP 2</span><h2>Select profile and slot</h2></div></div>
        <form method="post" action="{{ route('patient.appointments.store') }}">
            @csrf
            @if($recommendationId)
                <input type="hidden" name="recommendation_id" value="{{ $recommendationId }}">
            @endif
            <label>Patient profile
                <select name="patient_id" required>
                    @foreach($patients as $patient)
                        <option value="{{ $patient->patient_id }}" @selected((string) old('patient_id', $accountPatientId) === (string) $patient->patient_id)>{{ $patient->patient_id === $accountPatientId ? auth()->user()->full_name.' (You)' : $patient->full_name.' ('.$patient->relationship_to_account.')' }} · ID {{ $patient->patient_code }}</option>
                    @endforeach
                </select>
            </label>
            @forelse($slots->groupBy(fn ($slot) => $slot->slot_start->toDateString()) as $daySlots)
                <div class="panel-head">
                    <div>
                        <span class="kicker">{{ $daySlots->first()->slot_start->format('l') }}</span>
                        <h3>{{ $daySlots->first()->slot_start->format('d M Y') }}</h3>
                    </div>
                    <span class="muted">{{ $daySlots->count() }} available</span>
                </div>
                <div class="slot-grid">
                    @foreach($daySlots as $slot)
                        <label class="slot-option" data-schedule-expires="{{ \Carbon\Carbon::parse($slot->schedule->schedule_date->toDateString().' '.$slot->schedule->start_time)->getTimestamp() }}">
                            <input type="radio" name="slot_id" value="{{ $slot->slot_id }}" required>
                            <span><strong>{{ $slot->slot_start->format('h:i A') }}</strong><small>Available</small></span>
                        </label>
                    @endforeach
                </div>
            @empty
                <p class="muted">{{ $selectedDoctor ? 'No available slots for this doctor during the next 20 days.' : 'Choose a doctor to display availability for the next 20 days.' }}</p>
            @endforelse
            <button class="btn btn-primary btn-block" @disabled($slots->isEmpty())>Confirm appointment</button>
        </form>
    </section>
    @endif
</div>
@endsection

@push('scripts')<x-schedule-expiry-script/>@endpush
