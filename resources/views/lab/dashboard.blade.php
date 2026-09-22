@extends('layouts.app') @section('page-title','Laboratory Dashboard') @section('content')
<div class="stat-grid stat-grid-3">
<div class="stat-card">
<span>Pending / in progress</span>
<strong>{{ $pending }}</strong>
</div>
<div class="stat-card">
<span>Completed today</span>
<strong>{{ $completedToday }}</strong>
</div>
<div class="stat-card">
<span>Recent orders</span>
<strong>{{ $orders->count() }}</strong>
</div>
</div>
<section class="panel">
<div class="panel-head">
<h2>Recent laboratory orders</h2>
<a class="btn btn-primary btn-sm" href="{{ route('lab.orders') }}">Manage orders</a>
</div>
<div class="table-scroll">
<table>
<thead>
<tr>
<th>Order</th>
<th>Patient</th>
<th>Doctor</th>
<th>Tests</th>
<th>Status</th>
</tr>
</thead>
<tbody>@forelse($orders as $o)<tr>
<td>{{ $o->lab_order_no }}</td>
<td>{{ $o->patient->full_name }}</td>
<td>{{ $o->doctor?->user?->full_name ?: 'Reception booking' }}</td>
<td>{{ $o->items->pluck('test.test_name')->join(', ') }}</td>
<td>
<x-status :value="$o->status"/>
</td>
</tr>@empty<tr>
<td colspan="5">No laboratory orders.</td>
</tr>@endforelse</tbody>
</table>
</div>
</section>@endsection
