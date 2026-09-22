@extends('layouts.app')
@section('page-title','Doctor Schedules & Slots')
@section('content')
<div class="dashboard-grid align-start">
    <section class="panel">
        <h2>Create schedule & slots</h2>
        <form method="post" action="{{ route('staff.schedules.store') }}">
            @csrf
            <label>Doctor
                <select name="doctor_id" required>
                    <option value="">Select doctor</option>
                    @foreach($doctors as $doctor)
                        <option value="{{ $doctor->doctor_id }}" @selected(old('doctor_id')==$doctor->doctor_id)>{{ $doctor->user->full_name }}</option>
                    @endforeach
                </select>
            </label>
            <div class="form-grid">
                <label>Date<input type="date" name="schedule_date" value="{{ old('schedule_date') }}" min="{{ now()->toDateString() }}" required></label>
                <label>Room<input name="room_no" value="{{ old('room_no') }}" maxlength="30"></label>
                <label>Start<input type="time" name="start_time" value="{{ old('start_time') }}" required></label>
                <label>End<input type="time" name="end_time" value="{{ old('end_time') }}" required></label>
                <label>Slot minutes<input type="number" name="slot_minutes" value="{{ old('slot_minutes',15) }}" min="5" max="60" required></label>
                <label>Capacity<input type="number" name="capacity" value="{{ old('capacity',12) }}" min="1" max="100" required></label>
            </div>
            <button class="btn btn-primary">Generate schedule</button>
        </form>
    </section>
    <section class="recent-schedules">
        <h2>Recent schedules</h2>
        @forelse($schedules as $schedule)
            <article class="panel schedule-card">
                <div>
                    <strong>{{ $schedule->doctor->user->full_name }}</strong>
                    <p>{{ $schedule->schedule_date?->format('D, d M Y') }} · {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }}–{{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}</p>
                    <small>Room {{ $schedule->room_no ?: 'TBA' }} · {{ $schedule->slots->where('slot_status','AVAILABLE')->count() }} available slot(s)</small>
                </div>
                <x-status :value="$schedule->status"/>
            </article>
        @empty
            <x-empty title="No schedules created"/>
        @endforelse
        {{ $schedules->links() }}
    </section>
</div>
@endsection
