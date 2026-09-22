@extends('layouts.app') @section('page-title','Administration Dashboard') @section('content')<div class="stat-grid stat-grid-4"><div class="stat-card"><span>Portal users</span><strong>{{ $users }}</strong><a href="{{ route('admin.users') }}">Manage users</a></div><div class="stat-card"><span>Active doctors</span><strong>{{ $doctors }}</strong><a href="{{ route('admin.doctors') }}">Doctors</a></div><div class="stat-card"><span>Total appointments</span><strong>{{ $appointments }}</strong><a href="{{ route('staff.appointments') }}">Operations</a></div><div class="stat-card"><span>Recorded revenue</span><strong>Rs. {{ number_format($revenue,2) }}</strong><a href="{{ route('admin.analytics') }}">Analytics</a></div></div><div class="dashboard-grid"><section class="panel"><div class="panel-head"><h2>Weekly disease trends</h2><a class="btn btn-secondary btn-sm" href="{{ route('admin.analytics') }}">View analytics</a></div>@forelse($trends as $t)<div class="trend-row"><div><strong>{{ $t->condition->condition_name }}</strong><small>{{ $t->case_count }} finalized case(s)</small></div><b>{{ $t->percentage }}%</b></div>@empty<p class="muted">No trend data generated.</p>@endforelse</section><section class="panel admin-review-card">
    <div class="admin-review-heading">
        <span class="kicker">Doctor leave</span>
        <span class="admin-review-status {{ $pendingLeaves > 0 ? 'is-pending' : '' }}">{{ $pendingLeaves > 0 ? 'Needs review' : 'Up to date' }}</span>
    </div>
    <h2>{{ $pendingLeaves > 0 ? $pendingLeaves.' pending leave '.($pendingLeaves == 1 ? 'request' : 'requests') : 'No pending leave requests' }}</h2>
    <p class="muted">{{ $pendingLeaves > 0 ? 'Review requests and manage affected appointment slots.' : 'New doctor leave requests will appear here for review.' }}</p>
    <a class="btn btn-secondary btn-sm" href="{{ route('admin.doctors') }}">{{ $pendingLeaves > 0 ? 'Review leave requests' : 'Manage doctors' }}</a>
    <div class="admin-audit-section">
        <div>
            <h3>Activity &amp; audit log</h3>
            <p class="muted">Review recorded account activity and sensitive actions.</p>
        </div>
        <a class="admin-audit-link" href="{{ route('admin.audit') }}">View audit log <span aria-hidden="true">&rarr;</span></a>
    </div>
</section></div>@endsection