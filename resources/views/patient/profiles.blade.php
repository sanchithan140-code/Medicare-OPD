@extends('layouts.app')
@section('title','Family Profiles — MEDICARE')
@section('page-title','Family Patient Profiles')
@section('content')
<div class="page-actions">
    <div><h2>Separate records for every family member</h2><p class="muted">Each profile receives a permanent four-digit patient ID.</p></div>
    <button class="btn btn-primary" data-modal-open="profile-modal">Add profile</button>
</div>
<div class="profile-grid">
    @forelse($patients as $patient)
        <article class="family-profile-card">
            <div class="family-profile-heading">
                <div class="family-profile-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($patient->first_name, 0, 1)) }}</div>
                <div class="family-profile-identity">
                    <h3>{{ $patient->full_name }}</h3>
                    <p>{{ $patient->relationship_to_account }} &middot; {{ ucfirst(strtolower($patient->sex)) }}@if($patient->dob) &middot; {{ $patient->dob->age }} years @endif</p>
                </div>
                <span class="family-profile-id">ID {{ $patient->patient_code }}</span>
            </div>
            <dl class="family-profile-details">
                <div><dt>Phone number</dt><dd>{{ $patient->phone ?: 'Not provided' }}</dd></div>
                <div><dt>Blood group</dt><dd>{{ $patient->blood_group && $patient->blood_group !== 'UNKNOWN' ? $patient->blood_group : 'Not provided' }}</dd></div>
            </dl>
            <div class="family-profile-footer">
                <a class="btn btn-sm btn-secondary" href="{{ route('patient.profiles.edit', $patient) }}" aria-label="Edit profile for {{ $patient->full_name }}">Edit profile <span aria-hidden="true">&rarr;</span></a>
            </div>
        </article>
    @empty
        <x-empty title="Create the first patient profile" message="Your account needs at least one profile before booking."/>
    @endforelse
</div>
<div class="modal" id="profile-modal">
    <div class="modal-card modal-card-lg">
        <div class="panel-head"><div><span class="kicker">NEW PATIENT RECORD</span><h2>Add family patient profile</h2></div><button type="button" class="icon-btn" data-modal-close aria-label="Close">×</button></div>
        <form method="post" action="{{ route('patient.profiles.store') }}">@csrf
            <div class="form-grid">
                <label>Relationship<select name="relationship_to_account" required>@foreach(['Self','Mother','Father','Spouse','Son','Daughter','Child','Sibling','Other'] as $value)<option value="{{ $value }}" @selected(old('relationship_to_account') === $value)>{{ $value }}</option>@endforeach</select></label>
                <label>Sex<select name="sex" required>@foreach(['MALE','FEMALE','OTHER','UNKNOWN'] as $value)<option value="{{ $value }}" @selected(old('sex') === $value)>{{ $value }}</option>@endforeach</select></label>
                <label>First name<input name="first_name" value="{{ old('first_name') }}" minlength="2" maxlength="80" required></label>
                <label>Last name<input name="last_name" value="{{ old('last_name') }}" minlength="2" maxlength="80" required></label>
                <label>Date of birth<input type="date" name="dob" value="{{ old('dob') }}" min="1900-01-01" max="{{ now()->toDateString() }}"></label>
                <label>NIC (optional)<input name="nic_passport" value="{{ old('nic_passport') }}" maxlength="30" placeholder="200012345678 or 901234567V"></label>
                <label>Email<input type="email" name="email" value="{{ old('email') }}" maxlength="190"></label>
                <label>Phone<input name="phone" value="{{ old('phone') }}" inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="10" placeholder="0771234567"></label>
                <label class="span-2">Address<textarea name="address" maxlength="500">{{ old('address') }}</textarea></label>
                <label>Blood group<select name="blood_group"><option value="">Unknown</option>@foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-','UNKNOWN'] as $value)<option value="{{ $value }}" @selected(old('blood_group') === $value)>{{ $value }}</option>@endforeach</select></label>
                <label>Emergency contact name<input name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" maxlength="120"></label>
                <label>Emergency contact phone<input name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="10"></label>
                <label>Known allergen<input name="allergen" value="{{ old('allergen') }}" maxlength="150"></label>
                <label class="span-2">Allergy reaction<input name="allergy_reaction" value="{{ old('allergy_reaction') }}" maxlength="255"></label>
            </div>
            <div class="form-actions"><button class="btn btn-primary">Create secure profile</button></div>
        </form>
    </div>
</div>
@endsection
