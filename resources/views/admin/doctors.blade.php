@extends('layouts.app') @section('page-title','Doctors, Schedules & Leave') @section('content')<div class="dashboard-grid align-start"><div style="display:grid;gap:24px;align-content:start"><section class="panel"><h2>Create doctor</h2><form method="post" action="{{ route('admin.doctors.store') }}">@csrf<label>Doctor name<input name="full_name" value="{{ old('full_name') }}" maxlength="150" required></label><label>Email / username<input type="email" name="email" value="{{ old('email') }}" maxlength="190" autocomplete="off" required></label><label>Password<input type="password" name="password" minlength="8" autocomplete="new-password" required><small>Use at least 8 characters with uppercase, lowercase, numbers and symbols.</small></label><label>Specialty<select name="specialty_id">@foreach($specialties as $s)<option value="{{ $s->specialty_id }}" @selected(old('specialty_id') == $s->specialty_id)>{{ $s->specialty_name }}</option>@endforeach</select></label><label>Registration number<input name="registration_no" value="{{ old('registration_no') }}"></label><label>Qualifications<input name="qualifications_summary" value="{{ old('qualifications_summary') }}"></label><label>Consultation fee<input type="number" step=".01" name="consultation_fee" value="{{ old('consultation_fee') }}" required></label><label>Biography<textarea name="bio">{{ old('bio') }}</textarea></label><button class="btn btn-primary">Create doctor</button></form></section><section class="panel"><h2>Create schedule & slots</h2><form method="post" action="{{ route('admin.schedules.store') }}">@csrf<label>Doctor<select name="doctor_id">@foreach($doctors as $d)<option value="{{ $d->doctor_id }}">{{ $d->user->full_name }}</option>@endforeach</select></label><div class="form-grid"><label>Date<input type="date" name="schedule_date" min="{{ today()->toDateString() }}" required></label><label>Room<input name="room_no"></label><label>Start<input type="time" name="start_time" required></label><label>End<input type="time" name="end_time" required></label><label>Slot minutes<input type="number" name="slot_minutes" value="15" required></label><label>Capacity<input type="number" name="capacity" value="12" required></label></div><button class="btn btn-primary">Generate schedule</button></form></section></div><section><h2>Doctors</h2>@foreach($doctors as $d)
<details class="panel admin-doctor-schedules">
    <summary class="doctor-list-item">
        <div class="doctor-avatar">{{ strtoupper(substr($d->user->full_name,0,1)) }}</div>
        <div>
            <h3>{{ $d->user->full_name }}</h3>
            <p>{{ $d->specialty->specialty_name }} &middot; Rs. {{ number_format($d->consultation_fee,2) }}</p>
            <small>{{ $d->schedules->count() }} schedule(s) <span class="schedule-toggle-label">View schedules <span aria-hidden="true">&#9662;</span></span></small>
        </div>
        <x-status :value="$d->active?'ACTIVE':'INACTIVE'"/>
    </summary>
    <div class="admin-doctor-schedule-list">
        @forelse($d->schedules->sortBy([['schedule_date', 'asc'], ['start_time', 'asc']]) as $schedule)
            <div class="schedule-card">
                <div>
                    <strong>{{ $schedule->schedule_date->format('D, d M Y') }}</strong>
                    <p>{{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} &ndash; {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}</p>
                    <small>{{ $schedule->room_no ? 'Room: '.$schedule->room_no : 'Room not assigned' }}</small>
                </div>
                <x-status :value="$schedule->status"/>
            </div>
        @empty
            <p>No schedules created for this doctor yet.</p>
        @endforelse
    </div>
</details>
@endforeach{{ $doctors->links() }}<h2>Leave requests</h2>@foreach($leaves as $l)<article class="panel"><div class="panel-head"><div><h3>{{ $l->doctor->user->full_name }}</h3><p>{{ $l->start_at?->format('d M Y h:i A') }} – {{ $l->end_at?->format('d M Y h:i A') }}</p><small>{{ $l->reason }}</small></div><x-status :value="$l->status"/></div>@if($l->status==='REQUESTED')<div class="action-group">@foreach(['APPROVED','REJECTED'] as $v)<form method="post" action="{{ route('admin.leave.decide',$l) }}">@csrf<input type="hidden" name="status" value="{{ $v }}"><button class="btn {{ $v==='APPROVED'?'btn-primary':'btn-danger' }} btn-sm">{{ ucfirst(strtolower($v)) }}</button></form>@endforeach</div>@endif</article>@endforeach</section></div>@endsection