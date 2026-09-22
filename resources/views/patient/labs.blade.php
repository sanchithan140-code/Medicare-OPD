@extends('layouts.app')
@section('title','Laboratory Reports — MEDICARE')
@section('page-title','Laboratory Reports')
@section('content')
<div class="lab-grid">
@forelse($orders as $order)
<article class="panel lab-card"><div class="panel-head"><div><span class="kicker">{{ $order->lab_order_no }}</span><h2>{{ $order->patient->full_name }}</h2><p class="muted">Requested by {{ $order->doctor?->user?->full_name ?: 'Reception booking' }}</p></div><x-status :value="$order->status"/></div>
@foreach($order->items as $item)
<div class="lab-result patient-report-file"><div class="panel-head"><strong>{{ $item->test->test_name }}</strong><x-status :value="$item->status"/></div>
@if($item->result && $item->result->released_to_patient_at)
@if($item->result->attachments->isNotEmpty())<div class="file-actions">@foreach($item->result->attachments as $attachment)<a class="btn btn-sm btn-secondary" target="_blank" rel="noopener" href="{{ route('documents.view',$attachment) }}">View {{ $attachment->original_name }}</a><a class="btn btn-sm btn-ghost" href="{{ route('documents.download',$attachment) }}">Download</a>@endforeach</div>@elseif($item->result->result_text)<p style="white-space:pre-wrap">{{ $item->result->result_text }}</p>@else<p class="muted">No report file is available yet.</p>@endif
@else<p>Result is being processed or has not yet been verified for patient release.</p>@endif
</div>
@endforeach</article>
@empty<x-empty title="No laboratory orders" message="Digital test orders and verified reports will appear here."/>@endforelse
</div>{{ $orders->links() }}
@endsection
