@extends('layouts.app')
@section('page-title','Edit Patient Profile')
@section('content')
<section class="panel">
    <div class="panel-head">
        <div><span class="patient-code">PATIENT ID {{ $patient->patient_code }}</span><h2>Edit {{ $patient->full_name }}</h2><p class="muted">Update personal and optional health-contact information. The permanent patient ID cannot be changed.</p></div>
        <a class="btn btn-secondary" href="{{ route('patient.profiles') }}">Back to profiles</a>
    </div>
    @if($errors->any())
        <div class="alert alert-error"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <form method="post" action="{{ route('patient.profiles.update', $patient) }}">
        @csrf
        @method('PUT')
        <div class="form-grid">
            <label>Relationship<select name="relationship_to_account" required>@foreach(['Self','Mother','Father','Spouse','Son','Daughter','Child','Sibling','Other'] as $value)<option value="{{ $value }}" @selected(old('relationship_to_account', $patient->relationship_to_account) === $value)>{{ $value }}</option>@endforeach</select></label>
            <label>Sex<select name="sex" required>@foreach(['MALE','FEMALE','OTHER','UNKNOWN'] as $value)<option value="{{ $value }}" @selected(old('sex', $patient->sex) === $value)>{{ $value }}</option>@endforeach</select></label>
            <label>First name<input name="first_name" value="{{ old('first_name', $patient->first_name) }}" minlength="2" maxlength="80" required></label>
            <label>Last name<input name="last_name" value="{{ old('last_name', $patient->last_name) }}" minlength="2" maxlength="80" required></label>
            <label>Date of birth<input type="date" name="dob" value="{{ old('dob', $patient->dob?->format('Y-m-d')) }}" min="1900-01-01" max="{{ now()->toDateString() }}"></label>
            <label>NIC (optional)<input name="nic_passport" value="{{ old('nic_passport', $patient->nic_passport) }}" maxlength="30"></label>
            <label>Email (optional)<input type="email" name="email" value="{{ old('email', $patient->email) }}" maxlength="190"></label>
            <label>Phone (optional)<input name="phone" value="{{ old('phone', $patient->phone) }}" inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="10" placeholder="0771234567"></label>
            <label class="span-2">Address (optional)<textarea name="address" maxlength="500">{{ old('address', $patient->address) }}</textarea></label>
            <label>Blood group<select name="blood_group"><option value="">Unknown</option>@foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-','UNKNOWN'] as $value)<option value="{{ $value }}" @selected(old('blood_group', $patient->blood_group) === $value)>{{ $value }}</option>@endforeach</select></label>
            <div></div>
            <label>Emergency contact name<input name="emergency_contact_name" value="{{ old('emergency_contact_name', $emergencyContact?->contact_name) }}" maxlength="120"></label>
            <label>Emergency contact phone<input name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $emergencyContact?->phone) }}" inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="10"></label>
            <label>Known allergen<input name="allergen" value="{{ old('allergen', $allergy?->allergen) }}" maxlength="150"></label>
            <label>Allergy reaction<input name="allergy_reaction" value="{{ old('allergy_reaction', $allergy?->reaction) }}" maxlength="255"></label>
        </div>
        <div class="form-actions"><button class="btn btn-primary">Save profile changes</button></div>
    </form>
</section>
@endsection
