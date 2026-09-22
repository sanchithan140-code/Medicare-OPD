@extends('layouts.app')
@section('page-title','Reschedule Appointment')
@section('content')
<div class="dashboard-grid align-start">
    <section class="panel">
        <span class="kicker">CURRENT APPOINTMENT</span>
        <h2>{{ $appointment->doctor->user->full_name }}</h2>
        <p>{{ $appointment->doctor->specialty->specialty_name }}</p>
        <div class="detail-grid">
            <div><small>Patient</small><p>{{ $appointment->patient->full_name }}</p></div>
            <div><small>Current time</small><p>{{ $appointment->slot?->slot_start?->format('d M Y h:i A') }}</p></div>
        </div>
    </section>
    <section class="panel">
        <h2>Select a new slot</h2>
        <form method="post" action="{{ route('patient.appointments.reschedule.store',$appointment) }}">
            @csrf
            <div class="slot-grid">
                @forelse($slots as $slot)
                    <label class="slot-option" data-schedule-expires="{{ \Carbon\Carbon::parse($slot->schedule->schedule_date->toDateString().' '.$slot->schedule->start_time)->getTimestamp() }}">
                        <input type="radio" name="slot_id" value="{{ $slot->slot_id }}" required>
                        <span><strong>{{ $slot->slot_start->format('h:i A') }}</strong><small>{{ $slot->slot_start->format('d M Y') }}</small></span>
                    </label>
                @empty
                    <p>No future slots are currently available for this doctor.</p>
                @endforelse
            </div>
            <button class="btn btn-primary btn-block" @disabled($slots->isEmpty())>Confirm reschedule</button>
        </form>
    </section>
</div>
@endsection

@push('scripts')<x-schedule-expiry-script/>@endpush
