<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
class Appointment extends BaseModel {
    protected $primaryKey='appointment_id';
    protected $casts=['confirmed_at'=>'datetime','checked_in_at'=>'datetime','completed_at'=>'datetime','cancelled_at'=>'datetime','consultation_fee'=>'decimal:2','no_show_risk'=>'decimal:4'];
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class,'patient_id','patient_id'); }
    public function doctor(): BelongsTo { return $this->belongsTo(Doctor::class,'doctor_id','doctor_id'); }
    public function schedule(): BelongsTo { return $this->belongsTo(DoctorSchedule::class,'schedule_id','schedule_id'); }
    public function slot(): BelongsTo { return $this->belongsTo(AppointmentSlot::class,'slot_id','slot_id'); }
    public function consultation(): HasOne { return $this->hasOne(Consultation::class,'appointment_id','appointment_id'); }
    public function isWithinConsultationDate(): bool {
        return $this->slot?->slot_start !== null
            && $this->slot->slot_start->gte(today());
    }
    public function patientChangeDeadline() { return $this->created_at?->copy()->addDays(7); }
    public function allowsPatientChanges(): bool {
        return in_array($this->status,['BOOKED','CONFIRMED','RESCHEDULED'],true)
            && $this->created_at && now()->lt($this->patientChangeDeadline())
            && !$this->checked_in_at && !$this->completed_at && !$this->consultation()->exists();
    }
    public function recommendation(): BelongsTo { return $this->belongsTo(AiRecommendation::class,'ai_recommendation_id','recommendation_id'); }
}
