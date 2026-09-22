<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentReminder;
use App\Models\AppointmentSlot;
use App\Models\AppointmentStatusHistory;
use App\Models\Patient;
use App\Services\AI\AiGateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class AppointmentBookingService
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly NotificationService $notifications,
        private readonly AiGateway $ai,
        private readonly HospitalMailService $mail,
    ) {}

    public function book(Patient $patient, int $slotId, int $createdBy, string $source = 'PATIENT', ?int $recommendationId = null): Appointment
    {
        $features = $this->riskFeatures($patient);
        $baseRisk = min(0.95, 0.08 + ($features['previous_no_shows'] * 0.24) + ($features['previous_cancellations'] * 0.06));
        $riskLabel = $baseRisk >= 0.6 ? 'HIGH' : ($baseRisk >= 0.3 ? 'MEDIUM' : 'LOW');
        $riskExplanation = null;

        try {
            $aiRisk = $this->ai->explainNoShowRisk($features + ['calculated_risk_score' => $baseRisk]);
            if (in_array($aiRisk['risk_label'] ?? null, ['LOW','MEDIUM','HIGH'], true)) {
                $riskLabel = $aiRisk['risk_label'];
            }
            $riskExplanation = mb_substr((string) ($aiRisk['explanation'] ?? ''), 0, 450) ?: null;
        } catch (Throwable $exception) {
            report($exception);
        }

        $appointment = DB::transaction(function () use ($patient,$slotId,$createdBy,$source,$recommendationId,$baseRisk,$riskLabel,$riskExplanation) {
            Patient::where('patient_id',$patient->patient_id)->lockForUpdate()->firstOrFail();
            $slot = AppointmentSlot::query()->with('schedule.doctor.user')->lockForUpdate()->find($slotId);
            if (! $slot || $slot->slot_status !== 'AVAILABLE') {
                throw ValidationException::withMessages(['slot_id' => 'This appointment slot was just booked by another user. Choose another available time.']);
            }

            $schedule = $slot->schedule;
            if (!$patient->fresh()->active || ($schedule && \App\Models\DoctorLeave::where('doctor_id',$schedule->doctor_id)->where('status','APPROVED')->where('start_at','<',$slot->slot_end)->where('end_at','>',$slot->slot_start)->exists())) {
                throw ValidationException::withMessages(['slot_id'=>'This patient or appointment time is no longer available for booking.']);
            }
            if (! $schedule || ! $schedule->acceptsBookings() || ! $slot->slot_start->isFuture()) {
                throw ValidationException::withMessages(['slot_id' => 'The selected schedule is closed or has already passed.']);
            }

            $conflict = Appointment::where('patient_id',$patient->patient_id)
                ->where('doctor_id',$schedule->doctor_id)
                ->where('status','!=','CANCELLED')
                ->whereHas('slot', fn ($query) => $query->whereDate('slot_start',$slot->slot_start->toDateString()))
                ->exists();
            if ($conflict) {
                throw ValidationException::withMessages(['slot_id' => 'This patient already has an appointment with this doctor on the selected date. Reschedule the existing appointment to change its time.']);
            }

            $appointment = Appointment::create([
                'appointment_no' => $this->reference('APT'),
                'patient_id' => $patient->patient_id,
                'doctor_id' => $schedule->doctor_id,
                'schedule_id' => $schedule->schedule_id,
                'slot_id' => $slot->slot_id,
                'status' => 'BOOKED',
                'source' => $source,
                'ai_recommendation_id' => $recommendationId,
                'consultation_fee' => $schedule->doctor->consultation_fee,
                'no_show_risk' => $baseRisk,
                'no_show_risk_label' => $riskLabel,
                'booking_notes' => $riskExplanation,
                'created_by' => $createdBy,
            ]);

            $slot->update(['slot_status' => 'BOOKED']);
            AppointmentStatusHistory::create([
                'appointment_id' => $appointment->appointment_id,
                'to_status' => 'BOOKED',
                'reason' => "Appointment created through {$source} workflow",
                'changed_by' => $createdBy,
            ]);

            foreach ($this->reminderHours($riskLabel) as $hours) {
                $sendAt = $slot->slot_start->copy()->subHours($hours);
                if ($sendAt->isFuture()) {
                    AppointmentReminder::create([
                        'appointment_id' => $appointment->appointment_id,
                        'reminder_type' => $riskLabel === 'HIGH' ? 'HIGH_RISK' : 'STANDARD',
                        'scheduled_for' => $sendAt,
                        'channel' => 'EMAIL',
                        'status' => 'PENDING',
                    ]);
                }
            }

            return $appointment;
        }, 3);

        $appointment->load('patient.account','doctor.user','doctor.specialty','slot');
        $this->notifications->create(
            $patient->account_user_id,
            'appointment',
            'Appointment booked',
            "Your appointment {$appointment->appointment_no} has been booked and is awaiting Office Staff confirmation.",
            'Appointment',
            $appointment->appointment_id
        );
        $this->audit->record('appointment.created', $appointment, [], $appointment->toArray());

        return $appointment;
    }

    public function availableSlots(?int $doctorId = null, ?string $date = null)
    {
        return AppointmentSlot::query()
            ->with('schedule.doctor.user','schedule.doctor.specialty')
            ->where('slot_status','AVAILABLE')->withoutApprovedLeave()
            ->where('slot_start','>',now())
            ->when($doctorId, fn ($query) => $query->whereHas('schedule', fn ($schedule) => $schedule->where('doctor_id',$doctorId)))
            ->when($date, fn ($query) => $query->whereDate('slot_start',$date))
            ->whereHas('schedule', fn ($query) => $query->where('status','OPEN')->notStarted())
            ->orderBy('slot_start')
            ->get();
    }

    public function availableSlotsBetween(int $doctorId, string $startDate, string $endDate)
    {
        return AppointmentSlot::query()
            ->with('schedule.doctor.user','schedule.doctor.specialty')
            ->where('slot_status','AVAILABLE')->withoutApprovedLeave()
            ->where('slot_start','>',now())
            ->whereBetween('slot_start',[
                \Carbon\Carbon::parse($startDate)->startOfDay(),
                \Carbon\Carbon::parse($endDate)->endOfDay(),
            ])
            ->whereHas('schedule', fn ($schedule) => $schedule
                ->where('doctor_id',$doctorId)
                ->where('status','OPEN')->notStarted())
            ->orderBy('slot_start')
            ->get();
    }

    public function refreshPendingReminders(Appointment $appointment, string $skipReason = 'Appointment time changed'): void
    {
        $appointment->loadMissing('slot');
        AppointmentReminder::where('appointment_id',$appointment->appointment_id)
            ->where('status','PENDING')
            ->update(['status'=>'SKIPPED','error_message'=>$skipReason]);

        if (! $appointment->slot || ! in_array($appointment->status,['BOOKED','CONFIRMED','RESCHEDULED'],true)) {
            return;
        }

        foreach ($this->reminderHours((string) $appointment->no_show_risk_label) as $hours) {
            $sendAt = $appointment->slot->slot_start->copy()->subHours($hours);
            if ($sendAt->isFuture()) {
                AppointmentReminder::firstOrCreate(
                    [
                        'appointment_id'=>$appointment->appointment_id,
                        'reminder_type'=>$appointment->no_show_risk_label === 'HIGH' ? 'HIGH_RISK' : 'STANDARD',
                        'scheduled_for'=>$sendAt,
                    ],
                    ['channel'=>'EMAIL','status'=>'PENDING']
                );
            }
        }
    }

    public function skipPendingReminders(Appointment $appointment, string $reason): void
    {
        AppointmentReminder::where('appointment_id',$appointment->appointment_id)
            ->where('status','PENDING')
            ->update(['status'=>'SKIPPED','error_message'=>mb_substr($reason,0,500)]);
    }

    private function riskFeatures(Patient $patient): array
    {
        $history = Appointment::where('patient_id',$patient->patient_id)->get(['status']);
        return [
            'previous_appointments' => $history->count(),
            'previous_no_shows' => $history->where('status','NO_SHOW')->count(),
            'previous_cancellations' => $history->where('status','CANCELLED')->count(),
            'previous_completed' => $history->where('status','COMPLETED')->count(),
        ];
    }

    private function reminderHours(string $riskLabel): array
    {
        $hours = collect(explode(',', (string) env('APPOINTMENT_REMINDER_HOURS','24,4')))
            ->map(fn ($value) => (int) trim($value))->filter(fn ($value) => $value > 0)->values()->all();
        if ($riskLabel === 'HIGH' && ! in_array(48,$hours,true)) {
            array_unshift($hours,48);
        }
        return array_values(array_unique($hours));
    }

    private function reference(string $prefix): string
    {
        return $prefix.'-'.now()->format('ymd').'-'.strtoupper(substr(bin2hex(random_bytes(5)),0,8));
    }
}
