@extends('layouts.app')
@section('page-title','Reports & Analytics')
@section('content')
<div class="page-actions">
    <div><h2>Hospital operational analytics</h2><p class="muted">Appointments, completed transactions and finalized diagnosis information.</p></div>
    <form method="post" action="{{ route('admin.analytics.generate') }}">@csrf<button class="btn btn-primary">Generate weekly trends</button></form>
</div>

<div class="stat-grid stat-grid-3">
    <div class="stat-card accent"><span>Total incoming amount</span><strong>Rs. {{ number_format($totalRevenue,2) }}</strong><a href="#transactions">View transactions</a></div>
    <div class="stat-card"><span>Completed transactions</span><strong>{{ $payments->count() }}</strong><a href="#transactions">Latest 100 shown</a></div>
    <div class="stat-card"><span>Payment methods</span><strong>{{ $paymentByMethod->count() }}</strong></div>
</div>

<div class="dashboard-grid">
    <section class="panel">
        <h2>Appointments by status</h2>
        @php($maxA=max(1,(int)$appointmentByStatus->max('total')))
        @forelse($appointmentByStatus as $row)<div class="metric-bar"><div><strong>{{ str_replace('_',' ',$row->status) }}</strong><span>{{ $row->total }}</span></div><svg width="{{ ($row->total/$maxA)*100 }}%" height="11" aria-hidden="true" focusable="false"></svg></div>@empty<p>No appointments yet.</p>@endforelse
    </section>
    <section class="panel">
        <h2>Incoming payments by method</h2>
        @php($maxP=max(1,(float)$paymentByMethod->max('total')))
        @forelse($paymentByMethod as $row)<div class="metric-bar"><div><strong>{{ str_replace('_',' ',$row->payment_method) }}</strong><span>Rs. {{ number_format($row->total,2) }}</span></div><svg width="{{ ($row->total/$maxP)*100 }}%" height="11" aria-hidden="true" focusable="false"></svg></div>@empty<p>No completed payments yet.</p>@endforelse
    </section>
</div>

<section class="panel" id="transactions">
    <div class="panel-head"><div><span class="kicker">FINANCIAL LEDGER</span><h2>Incoming transactions</h2></div></div>
    <div class="table-scroll"><table>
        <thead><tr><th>Payment</th><th>Date</th><th>Patient</th><th>Booking / Invoice</th><th>Method</th><th>Reference</th><th>Amount</th></tr></thead>
        <tbody>
        @forelse($payments as $payment)
            <tr>
                <td>{{ $payment->payment_no }}</td>
                <td>{{ $payment->paid_at?->format('d M Y h:i A') }}</td>
                <td>{{ $payment->invoice->patient->full_name }}<small>{{ $payment->invoice->patient->patient_code }}</small></td>
                <td>{{ $payment->invoice->labOrder?->lab_order_no ?: $payment->invoice->appointment?->appointment_no ?: '—' }}<small>{{ $payment->invoice->invoice_no }}</small></td>
                <td>{{ str_replace('_',' ',$payment->payment_method) }}</td>
                <td>{{ $payment->transaction_reference ?: 'Hospital reception' }}</td>
                <td><strong>Rs. {{ number_format($payment->amount,2) }}</strong></td>
            </tr>
        @empty<tr><td colspan="7">No completed transactions recorded.</td></tr>@endforelse
        </tbody>
    </table></div>
</section>

<section class="panel">
    <h2>Weekly disease trends</h2>
    <div class="table-scroll"><table><thead><tr><th>Week</th><th>Condition</th><th>Cases</th><th>Share</th><th>Spike</th><th>Health insight</th></tr></thead><tbody>@foreach($trends as $trend)<tr><td>{{ $trend->week_start?->format('d M Y') }}</td><td>{{ $trend->condition->condition_name }}</td><td>{{ $trend->case_count }}</td><td>{{ $trend->percentage }}%</td><td>{{ $trend->is_spike?'YES':'No' }}</td><td>{{ $trend->ai_commentary ?: '—' }}</td></tr>@endforeach</tbody></table></div>
    {{ $trends->links() }}
</section>
@endsection
