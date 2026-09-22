@extends('layouts.app')
@section('page-title','Lab Bookings & Prices')
@section('content')
<div class="lab-booking-page">
    <section class="panel">
        <div class="panel-head"><div><h2>Book laboratory tests</h2><p class="muted">Find a patient, then select the tests to book.</p></div></div>
        <form class="lab-patient-search" method="get" action="{{ route('staff.lab-bookings') }}">
            <label>Patient ID<input name="patient_code" value="{{ request('patient_code') }}" placeholder="Enter patient ID" maxlength="50"></label>
            <button class="btn btn-primary" type="submit">Find patient</button>
            <a class="btn btn-secondary" href="{{ route('staff.lab-bookings') }}">All orders</a>
        </form>
        @if($patient)
            <div class="lab-patient-summary"><strong>{{ $patient->full_name }}</strong><span>{{ $patient->patient_code }}</span></div>
            <form method="post" action="{{ route('staff.lab-bookings.store') }}">
                @csrf
                <input type="hidden" name="patient_id" value="{{ $patient->patient_id }}">
                <fieldset class="lab-test-selection"><legend>Select tests</legend><div class="lab-test-grid">
                    @forelse($tests->where('active',true) as $test)
                        <label class="check-row lab-test-option"><input type="checkbox" name="tests[]" value="{{ $test->lab_test_id }}" @checked(in_array($test->lab_test_id, old('tests', [])))><span>{{ $test->test_name }}</span><strong>Rs. {{ number_format($test->standard_fee,2) }}</strong></label>
                    @empty<p class="muted">No active laboratory tests available.</p>@endforelse
                </div></fieldset>
                <div class="lab-booking-actions"><span class="muted">Review the invoice before making payment.</span><button class="btn btn-primary" @disabled($tests->where('active',true)->isEmpty())>Create order &amp; review payment</button></div>
            </form>
        @elseif(request('patient_code'))
            <p class="lab-booking-notice">No active patient found. Check the patient ID or create a patient profile at reception.</p>
        @endif
    </section>

    <details class="panel lab-price-panel">
        <summary>Laboratory test prices <span class="muted">View &amp; edit</span></summary>
        <p class="muted">Updated prices apply to new orders only.</p>
        <div class="lab-price-list">
            @forelse($tests as $test)
                <form method="post" action="{{ route('staff.lab-prices',$test) }}" class="lab-price-row">
                    @csrf
                    <div class="lab-price-name"><strong>{{ $test->test_name }}</strong>@unless($test->active)<small class="muted">Inactive</small>@endunless</div>
                    <label for="lab-price-{{ $test->lab_test_id }}">Price (Rs.)<input id="lab-price-{{ $test->lab_test_id }}" type="number" name="standard_fee" value="{{ number_format($test->standard_fee,2,'.','') }}" min="0" max="999999.99" step=".01" required></label>
                    <button class="btn btn-secondary btn-sm" aria-label="Save price for {{ $test->test_name }}">Save price</button>
                </form>
            @empty<p class="muted">No laboratory tests available.</p>@endforelse
        </div>
    </details>

    <section class="lab-orders-section" aria-labelledby="lab-orders-heading">
        <div class="panel-head"><h2 id="lab-orders-heading">Laboratory orders</h2><span class="muted">{{ $orders->total() }} {{ $orders->total() == 1 ? 'order' : 'orders' }}</span></div>
        <div class="lab-order-grid">
            @forelse($orders as $order)
                <article class="panel lab-order-card">
                    <div class="panel-head"><div><h3>{{ $order->lab_order_no }}</h3><p>{{ $order->patient->full_name }}</p><small class="muted">{{ $order->patient->patient_code }}</small></div><x-status :value="$order->status"/></div>
                    <div class="lab-order-payment"><span class="muted">Payment status</span><strong>{{ str_replace('_',' ', $order->invoice?->status ?: 'Not invoiced') }}</strong></div>
                    <ul class="lab-order-tests">@foreach($order->items as $item)<li><span>{{ $item->test->test_name }}</span><strong>Rs. {{ number_format($item->fee,2) }}</strong></li>@endforeach</ul>
                    <div class="lab-order-total"><span>Test total</span><strong>Rs. {{ number_format($order->items->sum('fee'),2) }}</strong></div>
                    <a class="btn btn-primary btn-sm" href="{{ route('lab-payment.show',$order) }}">Payment / receipt</a>
                    @if(!$order->consultation_id && $order->status==='PENDING' && $order->invoice?->status==='ISSUED')
                        <details class="lab-order-edit"><summary>Edit selected tests</summary><form method="post" action="{{ route('staff.lab-bookings.edit',$order) }}">@csrf
                            <div class="lab-edit-options">@foreach($tests->where('active',true) as $test)<label class="check-row"><input type="checkbox" name="tests[]" value="{{ $test->lab_test_id }}" @checked($order->items->contains('lab_test_id',$test->lab_test_id))>{{ $test->test_name }}</label>@endforeach</div>
                            <button class="btn btn-secondary btn-sm">Update tests &amp; invoice</button>
                        </form></details>
                    @endif
                </article>
            @empty<div class="panel empty-state">No laboratory orders found.</div>@endforelse
        </div>
        {{ $orders->links() }}
    </section>
</div>
@endsection