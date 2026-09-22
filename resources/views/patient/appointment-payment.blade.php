@extends('layouts.app')
@section('page-title','Appointment Payment')
@section('content')
<div class="dashboard-grid align-start">
    <section class="panel">
        <span class="kicker">BOOKING RESERVED</span>
        <h2>{{ $appointment->doctor->user->full_name }}</h2>
        <p>{{ $appointment->doctor->specialty->specialty_name }}</p>
        <div class="detail-grid">
            <div><small>Patient</small><p>{{ $appointment->patient->full_name }} · {{ $appointment->patient->patient_code }}</p></div>
            <div><small>Appointment</small><p>{{ $appointment->appointment_no }}</p></div>
            <div><small>Date and time</small><p>{{ $appointment->slot->slot_start->format('d M Y h:i A') }}</p></div>
            <div><small>Amount due</small><p><strong>Rs. {{ number_format($invoice->balance_amount,2) }}</strong></p></div>
        </div>
    </section>

    <section class="panel">
        <span class="kicker">PAYMENT METHOD</span>
        <h2>Complete payment</h2>
        @if($invoice->status==='VOID')
            <p>Invoice cancelled. No payment is due.</p>
            <a class="btn btn-secondary" href="{{ route('patient.appointments') }}">Back to appointments</a>
        @elseif((float)$invoice->balance_amount > 0)
            <form class="card-payment-form" data-card-payment method="post" action="{{ route('patient.payments.card',$invoice) }}">
                @csrf
                <label>Cardholder name<input name="cardholder_name" autocomplete="cc-name" maxlength="100" required></label>
                <label>Card number<input name="card_number" inputmode="numeric" autocomplete="cc-number" minlength="19" maxlength="19" pattern="[0-9]{4} [0-9]{4} [0-9]{4} [0-9]{4}" required></label>
                <div class="form-grid">
                    <label>Expiry (MM/YY)<input name="expiry" inputmode="numeric" placeholder="MM/YY" autocomplete="cc-exp" maxlength="5" pattern="(0[1-9]|1[0-2])/[0-9]{2}" required></label>
                    <label>CVV<input type="password" name="cvv" inputmode="numeric" autocomplete="cc-csc" pattern="[0-9]{3,4}" minlength="3" maxlength="4" required></label>
                </div>
                <button class="btn btn-primary btn-block">Pay Rs. {{ number_format($invoice->balance_amount,2) }} by card</button>
            </form>
            <div class="payment-alternative"><span>or</span></div>
            <form method="post" action="{{ route('patient.payments.hospital',$invoice) }}">
                @csrf
                <button class="btn btn-secondary btn-block">Pay directly at hospital</button>
            </form>
            
        @else
            <x-status value="PAID"/>
            <p>This appointment invoice has been paid.</p>
            <a class="btn btn-primary" href="{{ route('patient.payments') }}">View receipt</a>
        @endif
    </section>
</div>
@endsection
@push('styles')
<style>
.card-payment-form{display:grid;gap:20px;margin-top:24px}
.card-payment-form label{margin:0}
.card-payment-form .form-grid{gap:18px}
.payment-alternative{display:flex;align-items:center;gap:16px;margin:22px 0;color:#6c7e95;font-size:14px}
.payment-alternative::before,.payment-alternative::after{content:"";height:1px;background:#e0e8f2;flex:1}
</style>
@endpush
@push('scripts')
<script src="{{ asset('assets/js/card-payment.js') }}"></script>
@endpush
