@extends('layouts.app')
@section('page-title','Find a Doctor')
@section('content')
<section class="panel">
    <form class="filter-bar" method="get">
        <input name="q" value="{{ request('q') }}" placeholder="Doctor, specialty or qualification">
        <select name="specialty_id">
            <option value="">All specialties</option>
            @foreach($specialties as $specialty)
                <option value="{{ $specialty->specialty_id }}" @selected(request('specialty_id') == $specialty->specialty_id)>{{ $specialty->specialty_name }}</option>
            @endforeach
        </select>
        <button class="btn btn-primary">Search</button>
    </form>
</section>

<div class="doctor-grid">
    @forelse($doctors as $doctor)
        <article class="doctor-card">
            <div class="doctor-photo">
                <img src="{{ asset('images/doctor-a-rahman.png') }}" alt="Portrait of {{ $doctor->user->full_name }}">
            </div>
            <div class="doctor-body">
                <span class="kicker">{{ $doctor->specialty->specialty_name }}</span>
                <h3>{{ $doctor->user->full_name }}</h3>
                <p>{{ $doctor->qualifications_summary ?: 'Visiting consultant at Mediland Hospital' }}</p>
                <div class="doctor-meta">
                    <span>Fee: Rs. {{ number_format($doctor->consultation_fee,2) }}</span>
                    <span class="availability-dot">● Active</span>
                </div>
                <a class="btn btn-primary btn-block" href="{{ route('patient.appointments.book',['doctor_id'=>$doctor->doctor_id]) }}">Check availability</a>
            </div>
        </article>
    @empty
        <x-empty title="No matching doctors" message="Change the specialty or search term."/>
    @endforelse
</div>
{{ $doctors->links() }}
@endsection
