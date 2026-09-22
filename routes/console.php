<?php

use App\Models\AppointmentReminder;
use App\Services\TrendService;
use App\Services\HospitalMailService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () { $this->comment(Inspiring::quote()); });

Artisan::command('medicare:send-reminders', function (HospitalMailService $mail) {
    $due = AppointmentReminder::with('appointment.patient.account','appointment.doctor.user','appointment.slot')
        ->where('status', 'PENDING')->where('scheduled_for', '<=', now())->limit(200)->get();
    $sent=0;
    foreach($due as $reminder){
        if (!$reminder->appointment || !in_array($reminder->appointment->status,['BOOKED','CONFIRMED','RESCHEDULED'],true) || !$reminder->appointment->slot?->slot_start?->isFuture()) {
            $reminder->update(['status'=>'SKIPPED','error_message'=>'Appointment is no longer awaiting attendance.']);
            continue;
        }
        if($mail->reminder($reminder)){ $reminder->update(['status'=>'SENT','sent_at'=>now(),'error_message'=>null]); $sent++; }
        else { $reminder->update(['status'=>'FAILED','error_message'=>'Email delivery failed or patient email missing.']); }
    }
    $this->info("Processed {$due->count()} reminder(s); {$sent} sent.");
})->purpose('Process due appointment reminders');

Artisan::command('medicare:generate-trends', function (TrendService $service) {
    $this->info('Generated '.$service->generate().' weekly disease trend row(s).');
})->purpose('Generate weekly disease trend analytics');

Schedule::command('medicare:send-reminders')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('medicare:generate-trends')->weeklyOn(1, '01:00')->withoutOverlapping();
