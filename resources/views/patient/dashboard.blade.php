@extends('layouts.app') @section('page-title','Patient Dashboard') @section('content')<section class="welcome-banner">
<div>
<span class="kicker">Welcome, {{ auth()->user()->full_name }}</span>
<h2>Your healthcare, organized in one place.</h2>
<p>Every step counts. Choose a family profile, find available doctors and move forward with trusted care guidance.</p>
</div>
<div class="quick-actions">
<a class="btn btn-light" href="{{ route('patient.ai') }}">✦ Open Care Guide</a>
<a class="btn btn-secondary" href="{{ route('patient.appointments.book') }}">Book appointment</a>
</div>
</section>
<div class="stat-grid stat-grid-4">
<div class="stat-card">
<span>Family profiles</span>
<strong>{{ $patients->count() }}</strong>
<a href="{{ route('patient.profiles') }}">Manage profiles</a>
</div>
<div class="stat-card">
<span>Upcoming appointments</span>
<strong>{{ $upcoming->count() }}</strong>
<a href="{{ route('patient.appointments') }}">View all</a>
</div>
<div class="stat-card">
<span>Unread notices</span>
<strong>{{ $notifications->whereNull('read_at')->count() }}</strong>
<a href="{{ route('patient.notifications') }}">Open inbox</a>
</div>
<div class="stat-card">
<span>Health trend items</span>
<strong>{{ $trends->count() }}</strong>
<a href="#trends">Review trends</a>
</div>
</div>
<div class="dashboard-grid">
<section class="panel">
<div class="panel-head">
<div>
<span class="kicker">NEXT VISITS</span>
<h2>Upcoming appointments</h2>
</div>
<a class="btn btn-sm btn-primary" href="{{ route('patient.appointments.book') }}">New booking</a>
</div>@forelse($upcoming as $a)<article class="appointment-row">
<div class="date-tile">
<strong>{{ optional($a->slot?->slot_start)->format('d') }}</strong>
<span>{{ optional($a->slot?->slot_start)->format('M') }}</span>
</div>
<div>
<h3>{{ $a->doctor->user->full_name }}</h3>
<p>{{ $a->doctor->specialty->specialty_name }} · {{ optional($a->slot?->slot_start)->format('h:i A') }}</p>
<small>{{ $a->patient->full_name }} · {{ $a->appointment_no }}</small>
</div>
<x-status :value="$a->status" />
</article>@empty<x-empty title="No upcoming appointments" message="A healthier tomorrow can start today—choose a doctor or use the care guide to begin."/>@endforelse</section>
<section class="panel" id="trends">
<div class="panel-head">
<div>
<span class="kicker">WEEKLY AWARENESS</span>
<h2>Health trends</h2>
</div>
</div>@forelse($trends as $t)<div class="trend-row">
<div>
<strong>{{ $t->condition->condition_name }}</strong>
<small>Week of {{ $t->week_start?->format('d M Y') }}</small>
</div>
<b>{{ $t->percentage }}%</b>
</div>@empty<p class="muted">Trend analytics will appear after completed consultations are aggregated.</p>@endforelse
</section>
</div>@endsection
