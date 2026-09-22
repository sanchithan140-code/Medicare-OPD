@extends('layouts.app')
@section('page-title','Payments & Receipts')
@section('content')
<div class="table-card"><div class="table-scroll"><table>
    <thead><tr><th>Invoice</th><th>Patient</th><th>Services</th><th>Total</th><th>Paid</th><th>Balance</th><th>Payment / Action</th><th>Status</th></tr></thead>
    <tbody>
    @forelse($invoices as $invoice)
        <tr>
            <td>{{ $invoice->invoice_no }}</td>
            <td>{{ $invoice->patient->full_name }}<small>{{ $invoice->patient->patient_code }}</small></td>
            <td>{{ $invoice->items->pluck('description')->join(', ') }}</td>
            <td>Rs. {{ number_format($invoice->total_amount,2) }}</td>
            <td>Rs. {{ number_format($invoice->paid_amount,2) }}</td>
            <td>Rs. {{ number_format($invoice->balance_amount,2) }}</td>
            <td>
                @if((float)$invoice->balance_amount > 0 && $invoice->appointment_id)
                    <a class="btn btn-primary btn-sm" href="{{ route('patient.appointments.payment',$invoice->appointment_id) }}">Pay now</a>
                @else
                    @foreach($invoice->payments as $payment)
                        <strong>{{ $payment->payment_no }}</strong><x-status :value="$payment->status"/><small>{{ str_replace('_',' ',$payment->payment_method) }} · {{ $payment->paid_at?->format('d M Y h:i A') }}</small>
                        @if($payment->status==='COMPLETED' && str_contains($payment->notes ?? '', 'Hospital refund awaiting collection'))<small>Collect your refund at hospital reception.</small>@endif
                    @endforeach
                @endif
            </td>
            <td><x-status :value="$invoice->status"/></td>
        </tr>
    @empty
        <tr><td colspan="8">No invoices recorded.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
{{ $invoices->links() }}
@endsection
