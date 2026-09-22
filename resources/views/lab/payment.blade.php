@extends('layouts.app')
@section('page-title','Laboratory Payment')
@section('content')
<section class="panel lab-payment-card">
    <div class="panel-head lab-payment-heading">
        <div><span class="kicker">Laboratory order</span><h2>{{ $order->lab_order_no }}</h2></div>
        <x-status :value="$order->invoice?->status ?? $order->status"/>
    </div>
    <div class="lab-payment-patient">
        <div><span>Patient</span><strong>{{ $order->patient->full_name }}</strong></div>
        <div><span>Patient ID</span><strong>{{ $order->patient->patient_code }}</strong></div>
    </div>
    <div class="table-scroll lab-payment-tests">
        <table><thead><tr><th scope="col">Test</th><th scope="col">Fee</th></tr></thead>
            <tbody>@foreach($order->items as $item)<tr><td>{{ $item->test->test_name }}</td><td>Rs. {{ number_format($item->fee,2) }}</td></tr>@endforeach</tbody>
        </table>
    </div>
    <div class="lab-payment-summary">
        <div><span>Test total</span><strong>Rs. {{ number_format($order->items->sum('fee'),2) }}</strong></div>
        <div class="lab-payment-balance"><span>Balance due</span><strong>Rs. {{ number_format($order->invoice?->balance_amount ?? $order->items->sum('fee'),2) }}</strong></div>
    </div>
    @if($order->invoice?->status==='PAID')
        <div class="invoice-paid-message lab-payment-complete"><strong>Payment complete</strong><span>Ready for laboratory processing.</span></div>
    @elseif($order->status!=='CANCELLED')
        <form class="lab-payment-form" method="post" action="{{ route('lab-payment.store',$order) }}">
            @csrf
            <h3>Record payment</h3>
            <div class="form-grid">
                <label>Payment method<select name="method"><option value="CASH" @selected(old('method')==='CASH')>Cash</option><option value="CARD" @selected(old('method')==='CARD')>Card &middot; hospital terminal</option></select></label>
                <label>Terminal reference (optional)<input name="reference" value="{{ old('reference') }}" maxlength="100"></label>
            </div>
            <label class="check-row"><input type="checkbox" name="received" value="1" required>I received the displayed amount in cash or through the hospital card terminal.</label>
            <button class="btn btn-primary">Confirm payment</button>
        </form>
    @endif
    <div class="lab-payment-footer">
        @if($order->invoice)<a class="btn btn-secondary btn-sm" href="{{ route('lab-payment.receipt',$order->invoice) }}">View / print receipt</a>@endif
        @if(auth()->user()->isRole('lab_officer'))<a href="{{ route('lab.orders') }}">Back to laboratory orders</a>@else<a href="{{ route('staff.lab-bookings') }}">Back to laboratory bookings</a>@endif
    </div>
</section>
@endsection