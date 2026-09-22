@extends('layouts.app') @section('page-title','Schedule & Leave') @section('content')<div class="dashboard-grid align-start"><section class="panel"><div class="panel-head"><h2>Consultation schedules</h2></div>@forelse($schedules as $s)<article class="schedule-card" data-schedule-expires="{{ \Carbon\Carbon::parse($s->schedule_date->toDateString().' '.$s->end_time)->getTimestamp() }}"><div><strong>{{ $s->schedule_date?->format('D, d M Y') }}</strong><p>{{ \Carbon\Carbon::parse($s->start_time)->format('h:i A') }}–{{ \Carbon\Carbon::parse($s->end_time)->format('h:i A') }} · Room {{ $s->room_no ?: 'TBA' }}</p></div><div><x-status :value="$s->status"/><small>{{ $s->slots->where('slot_status','AVAILABLE')->count() }} available</small></div></article>@empty<x-empty title="No schedules created"/>@endforelse{{ $schedules->links() }}</section><div class="doctor-leave-column">
    <section class="panel doctor-leave-panel">
        <h2>Request leave</h2>
        <p class="muted leave-intro">Approved leave blocks new booking slots and alerts staff to affected appointments.</p>
        <form method="post" action="{{ route('doctor.leave.store') }}">
            @csrf
            <div class="leave-date-fields">
                <label>Start date &amp; time
                    <input type="datetime-local" name="start_at" value="{{ old('start_at') }}" required>
                </label>
                <label>End date &amp; time
                    <input type="datetime-local" name="end_at" value="{{ old('end_at') }}" required>
                </label>
            </div>
            <label>Reason
                <textarea name="reason" rows="4" placeholder="Briefly explain the reason for your leave" required>{{ old('reason') }}</textarea>
            </label>
            <div class="leave-form-actions">
                <button class="btn btn-primary" type="submit">Submit request</button>
            </div>
        </form>
    </section>
    <section class="panel doctor-leave-history">
        <h2>Recent requests</h2>
        @forelse($leaves as $l)
            <article class="leave-history-item">
                <div>
                    <strong>{{ $l->start_at?->format('d M Y, h:i A') }}</strong>
                    <small>to {{ $l->end_at?->format('d M Y, h:i A') }}</small>
                    <p>{{ $l->reason }}</p>
                </div>
                <x-status :value="$l->status"/>
            </article>
        @empty
            <p class="leave-empty">No leave requests yet. Your submitted requests will appear here.</p>
        @endforelse
    </section>
</div>
</div>
@endsection

@push('scripts')<x-schedule-expiry-script/>@endpush
