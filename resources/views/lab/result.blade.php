@extends('layouts.app')
@section('title','Laboratory Result — MEDICARE')
@section('page-title','Laboratory Result Entry')
@section('content')
<div class="dashboard-grid align-start">
<section class="panel"><span class="kicker">{{ $item->order->lab_order_no }}</span><h2>{{ $item->test->test_name }}</h2><div class="detail-grid"><div><small>Patient</small><p>{{ $item->order->patient->full_name }} ({{ $item->order->patient->patient_code }})</p></div><div><small>Ordering doctor</small><p>{{ $item->order->doctor?->user?->full_name ?: 'Reception booking' }}</p></div><div><small>Reference information</small><p>{{ $item->test->reference_notes ?: 'Refer to approved laboratory reference ranges.' }}</p></div><div><small>Current status</small><p><x-status :value="$item->status"/></p></div></div><div class="warning-check"><strong>Privacy rule:</strong> report documents are stored outside the public web directory. Email contains only a notification; the doctor and patient open the report securely after authorization.</div>
@if($attachments->isNotEmpty())<hr class="divider"><h3>Existing protected documents</h3>@foreach($attachments as $attachment)<div class="file-row"><span>📄 {{ $attachment->original_name }}</span><a class="btn btn-sm btn-secondary" target="_blank" href="{{ route('documents.view',$attachment->attachment_id) }}">Open</a></div>@endforeach @endif
</section>
<section class="panel"><form method="post" enctype="multipart/form-data" action="{{ route('lab.result.store',$item) }}">@csrf
<label>Result values / interpretation<textarea name="result_text" rows="12" maxlength="30000" placeholder="Enter measured values, units and reference ranges when available...">{{ old('result_text',$item->result?->result_text) }}</textarea><small>Either typed result content or a report file is required.</small></label>
<label>Protected report document<input type="file" name="report_file" accept="application/pdf,image/jpeg,image/png,.pdf,.jpg,.jpeg,.png"><small>PDF, JPG or PNG only. Maximum {{ config('ai.lab_result_max_mb',8) }} MB.</small></label>
<label class="check-row"><input type="checkbox" name="abnormal_flag" value="1" @checked(old('abnormal_flag',$item->result?->abnormal_flag))> Mark abnormal for doctor attention</label>
<label class="check-row warning-check"><input type="checkbox" name="verify_now" value="1" @checked(old('verify_now'))> Verify and release this result to the patient now</label>
<button class="btn btn-primary btn-block">Save, summarize & notify</button><p class="muted">A clear summary supports faster understanding. Clinical review always remains the responsibility of qualified staff.</p>
</form></section></div>
@endsection
