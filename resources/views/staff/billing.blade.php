@extends('layouts.app')
@section('page-title','Billing & Payments')
@section('content')
<section class="panel">
    <div class="panel-head"><div><span class="kicker">RECEPTION PAYMENT</span><h2>Find booking by patient ID</h2></div></div>
    <form class="filter-bar" method="get">
        <input name="q" value="{{ $search }}" placeholder="Enter patient ID or patient name" required>
        <button class="btn btn-primary">Find booking</button>
        @if($search)<a class="btn btn-secondary" href="{{ route('staff.billing') }}">Clear</a>@endif
    </form>
</section>

<div class="dashboard-grid align-start">
    <section class="panel">
        <h2>Create other invoice</h2>
        <form method="post" action="{{ route('staff.billing.store') }}">
            @csrf
            <label>Patient<select name="patient_id" required>@foreach($patients as $patient)<option value="{{ $patient->patient_id }}">{{ $patient->full_name }} ({{ $patient->patient_code }})</option>@endforeach</select></label>
            <label>Appointment ID (optional)<input type="number" name="appointment_id"></label>
            <label>Service type<select name="item_type">@foreach(['CONSULTATION','LABORATORY','PHARMACY','OTHER'] as $type)<option>{{ $type }}</option>@endforeach</select></label>
            <label>Description<input name="description" required></label>
            <label>Amount (LKR)<input type="number" step="0.01" min="0.01" name="amount" required></label>
            <button class="btn btn-primary">Create invoice</button>
        </form>
    </section>

    <section>
        <h2>{{ $search ? 'Matching bookings and invoices' : 'Recent invoices' }}</h2>
        @forelse($invoices as $invoice)
            <article class="panel invoice-card">
                <div class="panel-head">
                    <div><span class="kicker">{{ $invoice->invoice_no }}</span><h3>{{ $invoice->patient->full_name }}</h3><p class="invoice-patient-id">Patient ID: {{ $invoice->patient->patient_code }}</p></div>
                    <x-status :value="$invoice->status"/>
                </div>
                @if($invoice->labOrder)
                    <div class="summary-box"><strong>Laboratory tests</strong><p>{{ $invoice->labOrder->lab_order_no }}</p>
                        @foreach($invoice->items as $line)<p>{{ $line->description }} &middot; Rs. {{ number_format($line->line_total,2) }}</p>@endforeach
                    </div>
                @elseif($invoice->appointment)
                    <div class="summary-box">
                        <strong>{{ $invoice->appointment->doctor->user->full_name }}</strong>
                        <p>{{ $invoice->appointment->appointment_no }} · {{ $invoice->appointment->slot?->slot_start?->format('d M Y h:i A') }}</p>
                    </div>
                @endif
                <div class="detail-grid invoice-totals">
                    <div><small>Total</small><p>Rs. {{ number_format($invoice->total_amount,2) }}</p></div>
                    <div><small>Paid</small><p>Rs. {{ number_format($invoice->paid_amount,2) }}</p></div>
                    <div><small>Balance</small><p><strong>Rs. {{ number_format($invoice->balance_amount,2) }}</strong></p></div>
                </div>
                <a class="btn btn-secondary btn-sm" href="{{ route('staff.receipt',$invoice) }}">View / print receipt</a>
                @if($invoice->status==='VOID')
                    <p class="muted">Invoice cancelled.</p>
                    @foreach($invoice->payments as $payment)
                        @if($payment->status === 'COMPLETED' && str_starts_with((string)$payment->notes, 'Hospital refund awaiting collection; '))
                            <form method="post" action="{{ route('staff.refunds.collect', [$invoice, $payment]) }}">
                                @csrf
                                <p><strong>Refund awaiting collection: Rs. {{ number_format($payment->amount, 2) }}</strong><br><small>{{ $payment->payment_no }}</small></p>
                                <label class="check-row warning-check"><input type="checkbox" name="confirm_refund" value="1" required> I handed Rs. {{ number_format($payment->amount, 2) }} back to the patient.</label>
                                <button class="btn btn-primary">Confirm refund handed over</button>
                            </form>
                        @elseif($payment->status === 'REFUNDED')
                            <p class="muted">Refund recorded: Rs. {{ number_format($payment->amount, 2) }} &middot; {{ $payment->payment_no }}</p>
                        @endif
                    @endforeach
                @elseif($invoice->lab_order_id && (float)$invoice->balance_amount > 0)
                    <a class="btn btn-primary" href="{{ route('lab-payment.show',$invoice->labOrder) }}">Confirm cash / card payment</a>
                @elseif((float)$invoice->balance_amount > 0)
                    <form method="post" action="{{ route('staff.payments.store',$invoice) }}">
                        @csrf
                        <input type="hidden" name="payment_method" value="CASH">
                        <input type="hidden" name="amount" value="{{ $invoice->balance_amount }}">
                        <input type="hidden" name="transaction_reference" value="">
                        <label class="check-row warning-check"><input type="checkbox" name="confirm_payment" value="1" required> I received Rs. {{ number_format($invoice->balance_amount,2) }} from this patient.</label>
                        <button class="btn btn-primary btn-block">Confirm hospital payment</button>
                    </form>
                @else
                    <div class="invoice-paid-message"><strong>Payment complete</strong><span>No outstanding balance.</span></div>
                @endif
            </article>
        @empty
            <x-empty title="No matching unpaid booking" message="Check the patient ID and try again."/>
        @endforelse
        {{ $invoices->links() }}
    </section>
</div>
@endsection
