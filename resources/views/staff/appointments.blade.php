@extends('layouts.app')
@section('page-title','Appointment Operations')
@section('content')
<section class="panel">
    <div class="panel-head"><h2>Doctor sessions</h2><a class="btn btn-primary" href="{{ route('staff.appointments.book') }}">Book for patient</a></div>
    <form class="filter-bar" method="get" action="{{ route('staff.appointments') }}">
        <label>Doctor
            <select name="doctor_id" id="session-doctor-select">
                <option value="">All doctors</option>
                @foreach($doctors as $doctor)
                    <option value="{{ $doctor->doctor_id }}" data-registration="{{ $doctor->registration_no }}" @selected(request('doctor_id')==$doctor->doctor_id || (!request('doctor_id') && request('doctor_suffix') && $doctor->registration_no==='SLMC-900'.request('doctor_suffix')))>{{ $doctor->user->full_name }} &middot; {{ $doctor->specialty->specialty_name }} &middot; {{ $doctor->registration_no }}</option>
                @endforeach
            </select>
        </label>
        <label>Doctor registration ID<div class="doctor-id-input"><span>SLMC-900</span><input name="doctor_suffix" inputmode="numeric" pattern="[0-9]{3}" maxlength="3" placeholder="101" value="{{ request('doctor_suffix') }}" aria-label="Last three digits of doctor registration ID"></div></label>
        <label>Session date<input type="date" name="date" value="{{ request('date') }}"></label>
        <button class="btn btn-primary">Find sessions</button>
        <a class="btn btn-secondary" href="{{ route('staff.appointments') }}">Clear filters</a>
    </form>
    <p class="muted">Click a doctor session to see its patients. Confirm all applies only to pending bookings in that session.</p>
</section>
@forelse($sessions as $session)
<details class="panel staff-session">
    <summary class="panel-head">
        <div><h3>{{ $session->doctor->user->full_name }}</h3><p>{{ $session->doctor->registration_no }} &middot; {{ $session->doctor->specialty->specialty_name }}</p>
        <strong>{{ $session->schedule_date->format('D, d M Y') }} &middot; {{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }} &ndash; {{ \Carbon\Carbon::parse($session->end_time)->format('h:i A') }}</strong>
        <p>Room {{ $session->room_no ?: 'not assigned' }}</p></div>
        <span>{{ $session->appointments->count() }} patient(s) &middot; View patients &#9662;</span>
    </summary>
    @php($pendingCount=$session->appointments->whereIn('status',['BOOKED','RESCHEDULED'])->count())
    @if($pendingCount)
    <form method="post" action="{{ route('staff.appointments.confirm-session',$session) }}" class="session-confirm-action">@csrf
        <button class="btn btn-primary">Confirm all {{ $pendingCount }} pending appointments</button>
    </form>
    @endif
    <div class="table-scroll"><table><thead><tr><th>Time / Reference</th><th>Patient details</th><th>Status</th><th>Update</th></tr></thead><tbody>
    @foreach($session->appointments as $appointment)
        <tr><td>{{ $appointment->slot?->slot_start?->format('h:i A') }}<small>{{ $appointment->appointment_no }}</small></td>
        <td><strong>{{ $appointment->patient->full_name }}</strong><small>Patient ID: {{ $appointment->patient->patient_code }}</small><small>{{ $appointment->patient->phone }} &middot; {{ $appointment->patient->account?->is_walk_in ? 'Walk-in patient' : $appointment->patient->account?->email }}</small></td>
        <td><x-status :value="$appointment->status"/></td><td>@php($statusOptions = in_array($appointment->status,['BOOKED','RESCHEDULED'],true) ? ['CONFIRMED','NO_SHOW','CANCELLED'] : ($appointment->status==='CONFIRMED' ? ['CHECKED_IN','NO_SHOW','CANCELLED'] : []))
@if($statusOptions)<form method="post" action="{{ route('staff.appointments.status',$appointment) }}">@csrf<div class="inline-input"><select name="status" required>@foreach($statusOptions as $value)<option value="{{ $value }}">{{ str_replace('_',' ',$value) }}</option>@endforeach</select><input name="reason" maxlength="500" placeholder="Required for cancellation/no-show"><button class="btn btn-primary btn-sm">Save</button></div></form>@elseif($appointment->status==='CHECKED_IN')<span class="muted">Waiting for doctor</span>@else<span class="muted">Final state</span>@endif
        </td></tr>
    @endforeach
    </tbody></table></div>
</details>
@empty
<section class="panel"><x-empty title="No doctor sessions with appointments found"/></section>
@endforelse
{{ $sessions->links() }}
@endsection

@push('scripts')
<script>
(() => {
    const select = document.getElementById('session-doctor-select');
    const suffix = select.form.elements.doctor_suffix;
    select.addEventListener('change', () => {
        const match = (select.selectedOptions[0].dataset.registration || '').match(/^SLMC-900(\d{3})$/);
        suffix.value = match ? match[1] : '';
    });
    suffix.addEventListener('input', () => {
        const match = Array.from(select.options).find(option => option.dataset.registration === 'SLMC-900' + suffix.value);
        select.value = match ? match.value : '';
    });
})();
</script>
@endpush