@extends('layouts.app')
@section('title','Notifications — MEDICARE')
@section('page-title','Notifications')
@section('content')
<section class="panel"><div class="panel-head"><div><span class="kicker">PORTAL ACTIVITY</span><h2>Your notifications</h2></div></div>
<div class="notification-list">@forelse($notifications as $notification)<article class="notification-item"><div class="notification-icon">{{ $notification->read_at?'✓':'●' }}</div><div><strong>{{ $notification->title }}</strong><p>{{ $notification->message }}</p><small>{{ $notification->created_at?->format('d M Y h:i A') }} · {{ str_replace('_',' ',ucfirst($notification->notification_type)) }}</small></div></article>@empty<x-empty title="No notifications" message="Appointment, laboratory, payment and support updates will appear here."/>@endforelse</div>
</section>{{ $notifications->links() }}
@endsection
