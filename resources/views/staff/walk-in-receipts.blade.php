@extends('layouts.app')
@section('page-title','Walk-in Receipts')
@section('content')
<section class="panel">
    <h2>All walk-in receipts</h2>
    <p class="muted">Receipts are saved automatically when a walk-in booking is completed.</p>
    <form method="get" action="{{ route('staff.walkin-receipts') }}" class="filter-bar">
        <label>Patient ID, name or receipt number<input name="q" value="{{ request('q') }}" maxlength="100"></label>
        <label>Receipt date<input type="date" name="date" value="{{ request('date') }}"></label>
        <button class="btn btn-primary">Search</button>
        <a class="btn btn-secondary" href="{{ route('staff.walkin-receipts') }}">Clear filters</a>
    </form>
</section>
<div class="table-card"><div class="table-scroll"><table>
<thead><tr><th>Receipt / Date</th><th>Patient / ID</th><th>Doctor</th><th>Paid</th><th>Method</th><th>Receipt</th></tr></thead>
<tbody>@forelse($invoices as $invoice)
<tr><td><strong>{{ $invoice->invoice_no }}</strong><small>{{ $invoice->issued_at?->format('d M Y h:i A') }}</small></td>
<td>{{ $invoice->patient->full_name }}<small>{{ $invoice->patient->patient_code }}</small></td>
<td>{{ $invoice->appointment?->doctor?->user?->full_name }}</td>
<td>Rs. {{ number_format($invoice->paid_amount,2) }}</td>
<td>{{ $invoice->payments->where('status','COMPLETED')->pluck('payment_method')->unique()->join(', ') }}</td>
<td><a class="btn btn-secondary btn-sm" href="{{ route('staff.receipt',$invoice) }}">View / print</a></td></tr>
@empty<tr><td colspan="6">No walk-in receipts found.</td></tr>@endforelse</tbody>
</table></div></div>
{{ $invoices->links() }}
@endsection
