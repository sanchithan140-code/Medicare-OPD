<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AppointmentSlot extends BaseModel {
    protected $primaryKey='slot_id'; protected $casts=['slot_start'=>'datetime','slot_end'=>'datetime','held_until'=>'datetime'];
    public function schedule(): BelongsTo { return $this->belongsTo(DoctorSchedule::class,'schedule_id','schedule_id'); }
    public function scopeWithoutApprovedLeave(Builder $query): Builder {
        return $query->whereDoesntHave('schedule.doctor.leaves',fn($leave)=>$leave->where('status','APPROVED')
            ->whereColumn('doctor_leave.start_at','<','appointment_slots.slot_end')
            ->whereColumn('doctor_leave.end_at','>','appointment_slots.slot_start'));
    }
}
