@extends('layouts.app')
@section('page-title','Book for a Patient')
@section('content')
<div class="dashboard-grid align-start staff-booking-layout">
    <section class="panel">
        <span class="kicker">PHONE / WALK-IN BOOKING</span>
        <h2>Find available consultation slots</h2>
        <p class="muted">Use this workflow when a patient phones or visits reception and cannot book online.</p>
        <form method="get" action="{{ route('staff.appointments.book') }}" class="form-grid">
            <label>Doctor
                <select name="doctor_id" required>
                    <option value="">Select doctor</option>
                    @foreach($doctors as $doctor)
                        <option value="{{ $doctor->doctor_id }}" @selected($doctorId === $doctor->doctor_id)>{{ $doctor->user->full_name }} — {{ $doctor->specialty->specialty_name }}</option>
                    @endforeach
                </select>
            </label>
            <label>Date<input type="date" name="date" value="{{ $date }}" min="{{ now()->toDateString() }}" required></label>
            <div class="span-2"><button class="btn btn-secondary">Show available slots</button></div>
        </form>
    </section>

    <section class="panel">
        <span class="kicker">CONFIRM BOOKING</span>
        <h2>Select patient and slot</h2>
        <form method="post" action="{{ route('staff.appointments.book.store') }}">
            @csrf
            <label>Search by patient ID
                <input type="text" id="booking-patient-search" maxlength="50" autocomplete="off" aria-describedby="booking-patient-search-status">
            </label>
            <p id="booking-patient-search-status" class="muted" role="status" aria-live="polite"></p>
            <label>Patient profile
                <select name="patient_id" id="booking-patient-select" data-patient-codes="{{ $patients->pluck('patient_code', 'patient_id')->toJson() }}" required>
                    <option value="">Select patient</option>
                    @foreach($patients as $patient)
                        <option value="{{ $patient->patient_id }}">{{ $patient->full_name }} · {{ $patient->patient_code }} · {{ $patient->account?->is_walk_in ? 'Walk-in patient' : $patient->account?->email }}</option>
                    @endforeach
                </select>
            </label>
            <div class="slot-grid">
                @forelse($slots as $slot)
                    <label class="slot-option">
                        <input type="radio" name="slot_id" value="{{ $slot->slot_id }}" required>
                        <span><strong>{{ $slot->slot_start->format('h:i A') }}</strong><small>{{ $slot->slot_start->format('d M Y') }} · {{ $slot->schedule->doctor->user->full_name }}</small></span>
                    </label>
                @empty
                    <p class="muted span-all">Choose a doctor and date to display available slots.</p>
                @endforelse
            </div>
            <label>Booking note (optional)<textarea name="booking_notes" maxlength="500" placeholder="Phone booking, assistance required, preparation note..."></textarea></label>
            <button class="btn btn-primary btn-block" @disabled($slots->isEmpty())>Book & send patient confirmation</button>
        </form>
    </section>
</div>
@endsection
@push('scripts')
<script>
(() => {
    const search = document.getElementById('booking-patient-search');
    const select = document.getElementById('booking-patient-select');
    const status = document.getElementById('booking-patient-search-status');
    const options = Array.from(select.options).slice(1);
    const codes = JSON.parse(select.dataset.patientCodes);
    search.addEventListener('keydown', event => {
        if (event.key === 'Enter') {
            event.preventDefault();
            search.dispatchEvent(new Event('input'));
        }
    });
    search.addEventListener('input', () => {
        const query = search.value.trim().toLowerCase();
        const matches = options.filter(option => String(codes[option.value]).toLowerCase().includes(query));
        const previous = select.value;
        select.replaceChildren(new Option('Select patient', ''), ...matches);
        const exact = query ? matches.find(option => String(codes[option.value]).toLowerCase() === query) : null;
        select.value = exact ? exact.value : (!query && matches.some(option => option.value === previous) ? previous : '');
        status.textContent = !query ? '' : exact ? 'Patient found and selected.' : matches.length ? `${matches.length} matching patient(s). Select a profile below.` : 'No active patient found with this ID.';
    });
})();
</script>
@endpush
